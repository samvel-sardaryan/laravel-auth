@if (session('status'))
<p class="success">{{ session('status') }}</p>
@endif
@if (session('error'))
<p class="error">{{ session('error') }}</p>
@endif