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

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
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

   
    public function toArray(object $notifiable): array
    {
        $raw = $this->payment->status ?? 'waiting';
        if (in_array($raw, ['approved'])) {
            $status = 'approved';
            $icon = 'bi-check-circle-fill';
            $cls = 'ni-green';
            $message = 'Payment by ' . ($this->payment->name ?? '—') . ' has been approved.';
        } elseif (in_array($raw, ['rejected', 'accountant_rejected'])) {
            $status = 'rejected';
            $icon = 'bi-x-circle-fill';
            $cls = 'ni-red';
            $message = 'Payment by ' . ($this->payment->name ?? '—') . ' was rejected.';
        } else {
            $status = 'waiting';
            $icon = 'bi-hourglass-split';
            $cls = 'ni-gold';
            $message = 'New transaction from ' . ($this->payment->name ?? '—') . ' forwarded for your approval.';
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
}
