import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { setAssetTheme } from '@salon/core/lib/assetUrl'
import { resolveThemeId } from './resolveThemeId'
import './index.css'
import App from './App.jsx'

setAssetTheme(resolveThemeId())

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
