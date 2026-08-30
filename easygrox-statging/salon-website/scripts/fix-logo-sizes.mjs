import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themesDir = path.resolve(__dirname, '../themes')

const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory(),
)

for (const theme of themes) {
  for (const sub of ['components', 'src/components']) {
    const topBar = path.join(themesDir, theme, sub, 'TopBar.jsx')
    if (fs.existsSync(topBar)) {
      let content = fs.readFileSync(topBar, 'utf8')
      content = content.replace(
        /className="font-pacifico text-white[^"]*flex-shrink-0[^"]*"/,
        'className="inline-flex items-center flex-shrink-0 text-center sm:text-left transition-opacity duration-300 hover:opacity-90"',
      )
      content = content.replace(
        /<SalonLogo logoUrl=\{salon\.logo_url\} salonName=\{salon\.name\} \/>/,
        '<SalonLogo logoUrl={salon.logo_url} salonName={salon.name} variant="header" />',
      )
      fs.writeFileSync(topBar, content)
      console.log(`Updated ${theme}/${sub}/TopBar.jsx`)
    }

    const footer = path.join(themesDir, theme, sub, 'Footer.jsx')
    if (fs.existsSync(footer)) {
      let content = fs.readFileSync(footer, 'utf8')
      content = content.replace(
        /className="font-pacifico text-white[^"]*outline-none (focus-visible:ring-2 focus-visible:ring-[^"]+)"/,
        'className="inline-flex items-center transition-opacity duration-300 hover:opacity-90 outline-none $1"',
      )
      content = content.replace(
        /<SalonLogo\r?\n\s+logoUrl=\{salon\?\.logo_url\}\r?\n\s+salonName=\{salon\?\.name\}\r?\n\s+imageClassName="h-10 md:h-12 w-auto max-w-\[240px\] object-contain"\r?\n\s+placeholderClassName="([^"]+)"\r?\n\s+\/>/,
        '<SalonLogo\n                logoUrl={salon?.logo_url}\n                salonName={salon?.name}\n                variant="footer"\n                placeholderClassName="$1"\n              />',
      )
      fs.writeFileSync(footer, content)
      console.log(`Updated ${theme}/${sub}/Footer.jsx`)
    }
  }
}

console.log('Logo sizes updated in TopBar and Footer.')
