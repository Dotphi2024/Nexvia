@component('mail::message')
# Hello, Sir/Mam

{!! nl2br(e($body)) !!}

Best regards,<br>
The {{ config('mail.from.name') }} Team

@component('mail::button', ['url' => '#'])
Visit Our Website
@endcomponent

@endcomponent
