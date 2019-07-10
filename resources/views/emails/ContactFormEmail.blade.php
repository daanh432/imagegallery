@component('mail::message')
    # Contact form submission

    {{ config('mail.to.name') }} you received a message on {{ config('app.url') }}.

    First name: {{ $submission->name }}<br>
    Email address: {{ $submission->email }}<br>
    Message:<br>
    {{ $submission->message }}

    @component('mail::button', ['url' => 'mailto:' . $submission->email ])
        Respond to message
    @endcomponent

    Kind regards,<br>
    {{ config('app.name') }}
@endcomponent
