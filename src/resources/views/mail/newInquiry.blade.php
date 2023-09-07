@component('mail::message')
    {{ $content }}

    Name: {{ $name }}
    Phone Number: {{ $phoneNumber }}
@endcomponent
