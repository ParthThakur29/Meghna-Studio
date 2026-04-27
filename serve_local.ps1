$ip = '127.0.0.1'
$port = 8000
$listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Parse($ip), [int]$port)
$listener.Start()
Write-Output ("Serving on http://{0}:{1}/ (Press Ctrl+C to stop)" -f $ip, $port)

while ($true) {
    $client = $listener.AcceptTcpClient()
    Start-Job -ArgumentList $client -ScriptBlock {
        param($client)
        $stream = $client.GetStream()
        $reader = New-Object System.IO.StreamReader($stream)
        try {
            $requestLine = $reader.ReadLine()
            if (-not $requestLine) { $client.Close(); return }
            $parts = $requestLine -split ' '
            $path = $parts[1].TrimStart('/')
            if ([string]::IsNullOrEmpty($path)) { $path = 'index.html' }
            $file = Join-Path $PSScriptRoot $path
            if (-not (Test-Path $file)) {
                $body = [System.Text.Encoding]::UTF8.GetBytes("404 Not Found")
                $header = "HTTP/1.1 404 Not Found`r`nContent-Type: text/plain; charset=utf-8`r`nContent-Length: $($body.Length)`r`nConnection: close`r`n`r`n"
                $stream.Write([System.Text.Encoding]::ASCII.GetBytes($header), 0, $header.Length)
                $stream.Write($body, 0, $body.Length)
                $stream.Flush()
                $client.Close()
                return
            }
            $bytes = [System.IO.File]::ReadAllBytes($file)
            $contentType = switch -Regex ($file) {
                '\.html?$' { 'text/html; charset=utf-8' }
                '\.css$'   { 'text/css' }
                '\.js$'    { 'application/javascript' }
                '\.png$'   { 'image/png' }
                '\.jpe?g$' { 'image/jpeg' }
                '\.gif$'   { 'image/gif' }
                '\.svg$'   { 'image/svg+xml' }
                default     { 'application/octet-stream' }
            }
            $header = "HTTP/1.1 200 OK`r`nContent-Type: $contentType`r`nContent-Length: $($bytes.Length)`r`nConnection: close`r`n`r`n"
            $headerBytes = [System.Text.Encoding]::ASCII.GetBytes($header)
            $stream.Write($headerBytes, 0, $headerBytes.Length)
            $stream.Write($bytes, 0, $bytes.Length)
            $stream.Flush()
        } finally {
            $client.Close()
        }
    } | Out-Null
}
$listener.Stop()