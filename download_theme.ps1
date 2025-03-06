$url = "https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/download/"
$output = "theme.zip"
$start_time = Get-Date

Invoke-WebRequest -Uri $url -OutFile $output
Write-Output "Time taken: $((Get-Date).Subtract($start_time).Seconds) second(s)"

Expand-Archive -Path $output -DestinationPath "temp_theme"
Copy-Item -Path "temp_theme/NiceAdmin/*" -Destination "public/admin/assets" -Recurse
Remove-Item -Path $output
Remove-Item -Path "temp_theme" -Recurse
