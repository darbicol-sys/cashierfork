<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class NewMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected Payment $payment;
    protected $initiator;

    public function __construct(Payment $payment, $initiator = null)
    {
        $this->payment = $payment;
        $this->initiator = $initiator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $payer = $this->submitterName();
        $amount = number_format($this->payment->amount ?? 0, 2);

        $raw = $this->payment->status ?? 'waiting';

        if ($raw === 'forwarded') {
            // Approver-targeted email
            $url = route('accountant.approval', ['payment_id' => $this->payment->id]);
            $subject = "Transaction forwarded awaiting approval: {$payer}";
            $line = "A transaction of ₱{$amount} submitted by {$payer} has been forwarded and requires your final approval.";
        } elseif ($raw === 'approved') {
            // Approved: notify both parties that OR can be issued
            $op = $this->payment->op_number ?? null;
            $url = route('payments.index', ['search' => $op]);
            $subject = "Transaction approved: " . ($op ?? $payer);
            $line = "The transaction " . ($op ? "({$op}) " : '') . "submitted by {$payer} has been approved and is ready to be issued with an Official Receipt.";
        } elseif (in_array($raw, ['rejected','accountant_rejected'])) {
            // Rejected: include approver remarks if available
            $op = $this->payment->op_number ?? null;
            $url = route('payments.index', ['search' => $op]);
            $subject = "Transaction rejected: " . ($op ?? $payer);
            $notes = $this->payment->meta['accountant_remarks'] ?? $this->payment->meta['reviewer_remarks'] ?? null;
            $line = "The transaction " . ($op ? "({$op}) " : '') . "submitted by {$payer} has been rejected.";
            if ($notes) {
                $line .= "\n\nApprover notes: " . $notes;
            }
        } else {
            // Default: reviewer / maker notifications
            $url = route('reviewer', ['payment_id' => $this->payment->id]);
            $subject = "New transaction awaiting review: {$payer}";
            $line = "A new transaction of ₱{$amount} submitted by {$payer} requires your review.";
        }

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting('Hello')
                    ->line($line)
                    ->action('Review transaction', $url)
                    ->line('Thank you for reviewing transactions.')
                    ->salutation('DAR Cashier');
    }

   
    public function toArray(object $notifiable): array
    {
        $raw = $this->payment->status ?? 'waiting';
        $payer = $this->submitterName();

        if (in_array($raw, ['approved'])) {
            $status = 'approved';
            $icon = 'bi-check-circle-fill';
            $cls = 'ni-green';
            $message = "The transaction for {$payer} has been approved.";
        } elseif (in_array($raw, ['rejected', 'accountant_rejected'])) {
            $status = 'rejected';
            $icon = 'bi-x-circle-fill';
            $cls = 'ni-red';
            $message = "The transaction for {$payer} has been rejected.";
        } elseif (in_array($raw, ['forwarded'])) {
            $status = 'forwarded';
            $icon = 'bi-arrow-right-circle';
            $cls = 'ni-gold';
            $message = "The transaction for {$payer} has been forwarded.";
        } elseif (in_array($raw, ['under_review'])) {
            $status = 'modified';
            $icon = 'bi-pencil-square';
            $cls = 'ni-gold';
            $message = "The transaction for {$payer} has been modified.";
        } else {
            $status = 'waiting';
            $icon = 'bi-hourglass-split';
            $cls = 'ni-gold';
            $message = "New transaction from {$payer} has been submitted.";
        }

        return [
            'payment_id' => $this->payment->id,
            'name' => $this->payment->name,
            'op_number' => $this->payment->op_number,
            'status' => $status,
            'icon' => $icon,
            'cls' => $cls,
            'time' => now()->diffForHumans(),
            'message' => $message,
        ];
    }

    /**
     * Resolve the submitter/initiator display name.
     */
    private function submitterName()
    {
        if ($this->initiator) {
            $u = $this->initiator;
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? null);
            return $name ?: ($this->payment->name ?? '—');
        }

        try {
            $u = auth()->user();
            if ($u) {
                $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? null);
                return $name ?: ($this->payment->name ?? '—');
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $this->payment->name ?? '—';
    }
}
