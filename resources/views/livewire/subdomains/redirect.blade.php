<div>
@php use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
@endphp
@if(!empty($subdomains))
    @php
        $subdomain = $subdomains->first();

        // Get the base domain from config
        $baseDomain = config('tenancy.central_domains')[0];
        $currentScheme = request()->getScheme();
        $currentPort = request()->getPort();

        // Build the subdomain URL
        $subdomainHost = $subdomain->subdomain . '.' . $baseDomain;

        // Build the base URL with port if needed
        $baseUrl = $currentScheme . '://' . $subdomainHost;
        if ($currentPort && !in_array($currentPort, [80, 443])) {
            $baseUrl .= ':' . $currentPort;
        }

        // Generate a signed route for auto-login
        $fullUrl = URL::temporarySignedRoute(
            'auto-login',
            now()->addMinutes(10),
            [
                'user' => Auth::user()->id,
                'subdomain' => $subdomain->subdomain,
            ]
        );

        // Parse and rebuild the URL with the subdomain host
        $urlParts = parse_url($fullUrl);
        $fullUrl = $currentScheme . '://' . $subdomainHost;

        if ($currentPort && !in_array($currentPort, [80, 443])) {
            $fullUrl .= ':' . $currentPort;
        }

        $fullUrl .= ($urlParts['path'] ?? '');

        if (!empty($urlParts['query'])) {
            $fullUrl .= '?' . $urlParts['query'];
        }

        if (!empty($urlParts['fragment'])) {
            $fullUrl .= '#' . $urlParts['fragment'];
        }
    @endphp
    <script>
        window.location.href = "{!! $fullUrl !!}";
    </script>
@endif
</div>
