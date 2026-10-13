<x-mail::message>
# Hi {{ $name }},

Someone asked to reset the password for your {{ config('app.name') }} account. Click the button below to choose a new one.

<x-mail::button :url="$url">
Choose a new password
</x-mail::button>

This link expires in {{ $expiresIn }} minutes and works only once. If it has expired, ask for a new one on the login page.

If you didn't ask for this, you can ignore this email: your password stays the same.

See you inside,<br>
{{ config('app.name') }}
</x-mail::message>
