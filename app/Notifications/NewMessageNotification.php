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
    protected ?string $initiatorName = null;

    public function __construct(Payment $payment, $initiator = null)
    {
        $this->payment = $payment;

        // Normalize initiator into a simple display name to avoid
        // serializing whole User models or calling auth() inside
        // notifications (which can run outside the current request).
        if (is_object($initiator)) {
            $u = $initiator;
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? null);
            $this->initiatorName = $name ?: null;
        } else {
            $this->initiatorName = $initiator ? (string) $initiator : null;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Enable both database and mail channels so emails are sent.
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
            // Approver-targeted email — use fixed demo URL for mail links
            $url = 'https://cashierdemo.dar-bicol.com/';
            $subject = "Transaction forwarded awaiting approval: {$payer}";
            $line = "A transaction of ₱{$amount} submitted by {$payer} has been forwarded and requires your final approval.";
        } elseif ($raw === 'approved') {
            // Approved: notify both parties that OR can be issued
            $op = $this->payment->op_number ?? null;
            $url = 'https://cashierdemo.dar-bicol.com/';
            $subject = "Transaction approved: " . ($op ?? $payer);
            $line = "The transaction " . ($op ? "({$op}) " : '') . "submitted by {$payer} has been approved and is ready to be issued with an Official Receipt.";
        } elseif (in_array($raw, ['rejected','approver_rejected'])) {
            // Rejected: include approver remarks if available
            $op = $this->payment->op_number ?? null;
            $url = 'https://cashierdemo.dar-bicol.com/';
            $subject = "Transaction rejected: " . ($op ?? $payer);
            $notes = $this->payment->meta['Approver_remarks'] ?? $this->payment->meta['reviewer_remarks'] ?? null;
            $line = "The transaction " . ($op ? "({$op}) " : '') . "submitted by {$payer} has been rejected.";
            if ($notes) {
                $line .= "\n\nApprover notes: " . $notes;
            }
        } else {
            // Default: reviewer / maker notifications — link to demo site
            $url = 'https://cashierdemo.dar-bicol.com/';
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
        } elseif (in_array($raw, ['rejected', 'approver_rejected'])) {
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
        if ($this->initiatorName) {
            return $this->initiatorName;
        }

        return $this->payment->name ?? '—';
    }
}
