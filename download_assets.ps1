$urls = @{
    "bootstrap" = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    "bootstrap-icons" = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    "aos" = "https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css"
    "glightbox" = "https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/css/glightbox.min.css"
    "swiper" = "https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.css"
}

foreach ($item in $urls.GetEnumerator()) {
    $name = $item.Name
    $url = $item.Value
    $path = "public/front/assets/vendor/$name/css"
    
    # Create directory if it doesn't exist
    if (!(Test-Path $path)) {
        New-Item -ItemType Directory -Force -Path $path
    }
    
    # Download file
    $outputFile = "$path/$(Split-Path $url -Leaf)"
    Invoke-WebRequest -Uri $url -OutFile $outputFile
}
