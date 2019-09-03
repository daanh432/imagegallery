@component('mail::message')
# Contact form submission

You received a message on https://imagegallery.daanhendriks.nl/.

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
