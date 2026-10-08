Add-Type -AssemblyName System.Drawing

$sourceRoot = 'C:\Users\Le MADISON\OneDrive\Desktop\HEMIP-main\.work\pdf-assets'
$outputRoot = 'C:\Users\Le MADISON\OneDrive\Desktop\HEMIP-main\config\assets\img'
$images = [ordered]@{
    'hemip-campus.jpg' = 'preview-07.jpg'
    'hemip-graduation.jpg' = 'pdf-image-01-658x485.png'
    'hemip-atelier.jpg' = 'pdf-image-02-509x448.png'
    'hemip-etudiants.jpg' = 'pdf-image-03-501x771.png'
    'hemip-ceremonie.jpg' = 'pdf-image-04-1080x810.png'
    'hemip-rencontre.jpg' = 'pdf-image-05-1080x810.png'
    'hemip-informatique.jpg' = 'pdf-image-06-526x358.png'
    'hemip-vie-ecole.jpg' = 'pdf-image-08-1080x810.png'
}

$jpegEncoder = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() |
    Where-Object { $_.MimeType -eq 'image/jpeg' } |
    Select-Object -First 1
$quality = [System.Drawing.Imaging.EncoderParameters]::new(1)
$quality.Param[0] = [System.Drawing.Imaging.EncoderParameter]::new(
    [System.Drawing.Imaging.Encoder]::Quality,
    [long]86
)

foreach ($entry in $images.GetEnumerator()) {
    $sourcePath = Join-Path $sourceRoot $entry.Value
    $outputPath = Join-Path $outputRoot $entry.Key
    if (-not (Test-Path -LiteralPath $sourcePath)) {
        throw "Image source absente: $sourcePath"
    }

    $source = [System.Drawing.Image]::FromFile($sourcePath)
    $bitmap = $null
    $graphics = $null
    try {
        $scale = [Math]::Min(1.0, 1920.0 / [Math]::Max($source.Width, $source.Height))
        $width = [Math]::Max(1, [int][Math]::Round($source.Width * $scale))
        $height = [Math]::Max(1, [int][Math]::Round($source.Height * $scale))
        $bitmap = [System.Drawing.Bitmap]::new($width, $height, [System.Drawing.Imaging.PixelFormat]::Format24bppRgb)
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        $graphics.Clear([System.Drawing.Color]::White)
        $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
        $graphics.DrawImage($source, 0, 0, $width, $height)
        $bitmap.Save($outputPath, $jpegEncoder, $quality)
        [PSCustomObject]@{
            File = $entry.Key
            Width = $width
            Height = $height
            KB = [Math]::Round((Get-Item -LiteralPath $outputPath).Length / 1KB)
        }
    }
    finally {
        if ($graphics) { $graphics.Dispose() }
        if ($bitmap) { $bitmap.Dispose() }
        $source.Dispose()
    }
}

$quality.Dispose()
