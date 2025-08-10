<div>
@php use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
@endphp
@if(!empty($subdomains))
    @php
        $subdomain = $subdomains->first();
        // Generate a signed route for auto-login
        $fullUrl = URL::temporarySignedRoute(
            'auto-login',
            now()->addMinutes(10),
            [
                'user' => Auth::user()->id,
                'subdomain' => $subdomain->subdomain,
            ]
        );
    @endphp
    <script>
        window.location.href = "{!! $fullUrl !!}";
    </script>
@endif
</div>
