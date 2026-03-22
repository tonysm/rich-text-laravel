let fs = require('fs')
let crypto = require('crypto')
let path = require('path')
let esbuild = require('esbuild')

let distDir = path.resolve(__dirname, '../resources/dist')

let hashedFiles = {
    'node_modules/@37signals/lexxy/dist/stylesheets/lexxy.css': 'lexxy.css',
    'node_modules/trix/dist/trix.css': 'trix.css',
    'resources/css/lexxy-rich-text-laravel-attachments.css': 'lexxy-rich-text-laravel-attachments.css',
    'resources/css/lexxy-rich-text-laravel-daisyui.css': 'lexxy-rich-text-laravel-daisyui.css',
    'resources/css/lexxy-rich-text-laravel-flux.css': 'lexxy-rich-text-laravel-flux.css',
    'resources/css/trix-rich-text-laravel.css': 'trix-rich-text-laravel.css',
    'resources/css/trix-rich-text-laravel-attachments.css': 'trix-rich-text-laravel-attachments.css',
}

// We'll ship these files in ESM for Importmaps Laravel...
let esmFiles = {
    'node_modules/@37signals/lexxy/dist/lexxy.esm.js': 'lexxy.esm.js',
    'node_modules/trix/dist/trix.esm.js': 'trix.esm.js',
}

if (fs.existsSync(distDir)) {
    fs.rmSync(distDir, { recursive: true })
}

fs.mkdirSync(distDir, { recursive: true })

async function build() {
    let manifest = {}

    for (let [src, dest] of Object.entries(hashedFiles)) {
        let outfile = path.join(distDir, dest)

        await esbuild.build({
            entryPoints: [src],
            outfile,
            bundle: true,
            minify: true,
        })

        let contents = fs.readFileSync(outfile)
        let hash = crypto.createHash('md5').update(contents).digest('hex').slice(0, 8)
        let ext = path.extname(dest)
        let name = dest.replace(ext, '')
        let hashedName = `${name}-${hash}${ext}`

        fs.renameSync(outfile, path.join(distDir, hashedName))
        manifest[`/${dest}`] = `/vendor/rich-text-laravel/${hashedName}`
    }

    for (let [src, dest] of Object.entries(esmFiles)) {
        let outfile = path.join(distDir, dest)

        await esbuild.build({
            entryPoints: [src],
            outfile,
            bundle: true,
            minify: true,
            format: 'esm',
            external: ['@rails/activestorage'],
        })

        manifest[`/${dest}`] = `/vendor/rich-text-laravel/${dest}`
    }

    fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify(manifest, null, 2) + '\n')

    console.log('Build complete. Manifest:')
    console.log(manifest)
}

build().catch(() => process.exit(1))
