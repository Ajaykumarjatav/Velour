import fs from "fs";
import path from "path";

const UI = ["Chip", "Label", "H1", "H2", "H3", "H4", "Body", "Btn", "Card", "Divider", "Div", "IcBox"];
let failed = false;

function walk(dir) {
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, ent.name);
    if (ent.isDirectory()) walk(p);
    else if (p.endsWith(".jsx")) {
      const t = fs.readFileSync(p, "utf8");
      const uiImp = t.match(/import \{([^}]+)\} from ["'][^"']*\/ui["']/);
      const imported = uiImp ? uiImp[1].split(",").map((s) => s.trim()) : [];
      const missingUi = UI.filter((c) => new RegExp(`<${c}[\\s/>]`).test(t) && !imported.includes(c));
      if (missingUi.length) {
        console.log(p, "→ UI:", missingUi.join(", "));
        failed = true;
      }
      if (/<Ic[\s/>]/.test(t) && !/import\s+\{[^}]*\bIc\b/.test(t) && !/import\s+Ic\b/.test(t)) {
        console.log(p, "→ missing: Ic (from icons/Icon)");
        failed = true;
      }
      const themeUsed = ["C", "F", "R"].filter((k) => new RegExp(`\\b${k}\\.`).test(t));
      const themeImp = t.match(/import\s+\{([^}]+)\}\s+from\s+["'][^"']*theme["']/);
      const themeImported = themeImp ? themeImp[1].split(",").map((s) => s.trim().split(/\s+as\s+/)[0]) : [];
      const missingTheme = themeUsed.filter((k) => !themeImported.includes(k));
      if (missingTheme.length) {
        console.log(p, "→ theme:", missingTheme.join(", "));
        failed = true;
      }
    }
  }
}

walk(path.resolve("src"));
if (failed) process.exit(1);
