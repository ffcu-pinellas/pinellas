$urls = @(
    "https://logo.clearbit.com/huntington.com",
    "https://logo.clearbit.com/capitalone.com",
    "https://logo.clearbit.com/usbank.com",
    "https://logo.clearbit.com/td.com",
    "https://logo.clearbit.com/truist.com",
    "https://logo.clearbit.com/key.com",
    "https://logo.clearbit.com/regions.com",
    "https://logo.clearbit.com/discover.com",
    "https://logo.clearbit.com/santander.com",
    "https://logo.clearbit.com/bmo.com",
    "https://logo.clearbit.com/navyfederal.org",
    "https://logo.clearbit.com/usaa.com",
    "https://logo.clearbit.com/schwab.com",
    "https://logo.clearbit.com/synchrony.com",
    "https://logo.clearbit.com/firstcitizens.com",
    "https://logo.clearbit.com/ncsecu.org",
    "https://logo.clearbit.com/mtb.com",
    "https://logo.clearbit.com/53.com",
    "https://logo.clearbit.com/ally.com",
    "https://logo.clearbit.com/suncoastcreditunion.com",
    "https://logo.clearbit.com/americafirst.com",
    "https://logo.clearbit.com/penfed.org",
    "https://logo.clearbit.com/golden1.com"
)

$outDir = "public\assets\images\bank_logos"
if (-not (Test-Path $outDir)) {
    New-Item -ItemType Directory -Force -Path $outDir
}

foreach ($url in $urls) {
    # Generate MD5 hash for filename (poor man's MD5 in PowerShell)
    $md5 = [System.Security.Cryptography.MD5]::Create()
    $utf8 = [System.Text.Encoding]::UTF8
    $hash = [BitConverter]::ToString($md5.ComputeHash($utf8.GetBytes($url))).Replace("-", "").ToLower()
    $filename = "logo_" + $hash + ".png"
    $path = Join-Path $outDir $filename

    Write-Host "Downloading $url"
    try {
        # Using Google's high-res favicon service instead since clearbit blocks automated requests
        $domain = $url.Replace("https://logo.clearbit.com/", "")
        $googleUrl = "https://www.google.com/s2/favicons?domain=$domain&sz=128"
        Invoke-WebRequest -Uri $googleUrl -OutFile $path -UseBasicParsing
    } catch {
        Write-Host "Failed to download $url"
    }
}
