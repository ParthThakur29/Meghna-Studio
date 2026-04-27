$port = 8000
$prefix = "http://+:$port/"
$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($prefix)
$listener.Start()
Write-Output "Serving on http://localhost:$port/ (Press Ctrl+C to stop)"

while ($listener.IsListening) {
    try {
        $context = $listener.GetContext()
    } catch {
        break
    }
    $request = $context.Request
    $response = $context.Response

    $path = $request.Url.AbsolutePath.TrimStart('/')
    if ([string]::IsNullOrEmpty($path)) { $path = 'index.html' }

    $file = Join-Path $PSScriptRoot $path
    if (-not (Test-Path $file)) {
        $response.StatusCode = 404
        $buffer = [System.Text.Encoding]::UTF8.GetBytes("404 Not Found")
        $response.ContentLength64 = $buffer.Length
        $response.OutputStream.Write($buffer, 0, $buffer.Length)
        $response.OutputStream.Close()
        continue
    }

    $bytes = [System.IO.File]::ReadAllBytes($file)
    switch -Regex ($file) {
        '\.html?$' { $response.ContentType = 'text/html; charset=utf-8' }
        '\.css$'   { $response.ContentType = 'text/css' }
        '\.js$'    { $response.ContentType = 'application/javascript' }
        '\.png$'   { $response.ContentType = 'image/png' }
        '\.jpe?g$' { $response.ContentType = 'image/jpeg' }
        '\.gif$'   { $response.ContentType = 'image/gif' }
        '\.svg$'   { $response.ContentType = 'image/svg+xml' }
        '\.json$'  { $response.ContentType = 'application/json' }
        default     { $response.ContentType = 'application/octet-stream' }
    }
    $response.ContentLength64 = $bytes.Length
    try { $response.OutputStream.Write($bytes, 0, $bytes.Length) } finally { $response.OutputStream.Close() }
}
$listener.Stop()