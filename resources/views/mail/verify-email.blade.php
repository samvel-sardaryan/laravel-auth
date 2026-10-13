<x-mail::message>
# Hi {{ $name }},

Thanks for joining {{ config('app.name') }}. One last step: confirm that this email address belongs to you.

<x-mail::button :url="$url">
Confirm my email
</x-mail::button>

This link expires in {{ $minutes }} minutes. If it has expired, log in and ask for a new one.

If you didn't create an account, you can ignore this email.

See you inside,<br>
{{ config('app.name') }}
</x-mail::message>
