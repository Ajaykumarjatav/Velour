/**
 * Regenerates all src/ modules from velour.jsx (source of truth).
 * Run: npm run sync
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, "..");
const VEL = path.join(ROOT, "velour.jsx");
const OUT = path.join(ROOT, "src");

const lines = fs.readFileSync(VEL, "utf8").split("\n");

function slice(start, end) {
  return lines.slice(start - 1, end).join("\n");
}

function exportify(code, { defaultFn } = {}) {
  let c = code;
  if (defaultFn) {
    c = c.replace(new RegExp(`^export default function ${defaultFn}\\b`, "m"), `export default function ${defaultFn}`);
    c = c.replace(new RegExp(`^function ${defaultFn}\\b`, "m"), `export default function ${defaultFn}`);
  }
  c = c.replace(/^function (\w+)/gm, "export function $1");
  c = c.replace(/^const (\w+) = /gm, "export const $1 = ");
  return c;
}

function write(rel, imports, body, opts) {
  const file = path.join(OUT, rel);
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, `${imports}\n\n${exportify(body, opts)}\n`, "utf8");
  console.log("  ", rel);
}

const featureImports = `import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../../components/ui";
import { FeaturePageShell } from "../../components/layout/FeaturePageShell";
import {
  StatBadge, WorkflowStep, PainGain, Scenario, Quote,
  CalendarMock, POSMock, AnalyticsMock, ClientMock, StaffMock, WebsiteMock,
} from "../../components/shared/marketingBlocks";`;

const pageImports = `import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";`;

const authImports = `import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Btn, Body, H1 } from "../../components/ui";`;

console.log("Syncing from velour.jsx → src/\n");

// Live app auth URL (from velour.jsx)
const appAuthBody = `export function goToAppAuth() {
  if (typeof window === "undefined") return;
  const prefix = window.location.pathname.startsWith("/vellor") ? "/vellor" : "";
  window.location.href = \`\${window.location.origin}\${prefix}/admin/login\`;
}
`;
fs.mkdirSync(path.join(OUT, "constants"), { recursive: true });
fs.writeFileSync(path.join(OUT, "constants/appAuth.js"), appAuthBody + "\n");
console.log("  constants/appAuth.js");

// theme
const themeBody = slice(23, 32)
  .replace(/^const C = /m, "export const C = ")
  .replace(/^const F = /m, "export const F = ")
  .replace(/^const R = /m, "export const R = ");
fs.mkdirSync(path.join(OUT, "constants"), { recursive: true });
fs.writeFileSync(path.join(OUT, "constants/theme.js"), themeBody + "\n");
console.log("  constants/theme.js");

const brandBody = `const base = import.meta.env.BASE_URL;

export const brandLogoLight = \`\${base}images/easygrox-logo-light.png\`;
export const brandLogoDark = \`\${base}images/easygrox-logo-dark.png\`;
export const brandIcon = \`\${base}images/easygrox-icon.png\`;
`;
fs.writeFileSync(path.join(OUT, "constants/brand.js"), brandBody + "\n");
console.log("  constants/brand.js");

const chunks = [
  ["hooks/useWindowWidth.js", `import { useState, useEffect } from "react";`, slice(44, 52)],
  ["components/icons/iconSet.jsx", `import React from "react";`, slice(55, 103)],
  ["components/icons/Icon.jsx", `import React, { cloneElement } from "react";\nimport { I } from "./iconSet";`, slice(105, 121)],
  [
    "components/ui/index.jsx",
    `import React, { useState } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { Ic } from "../icons/Icon";`,
    slice(124, 192),
  ],
  ["components/mockups/DashMock.jsx", `import React from "react";\nimport { C, F, R } from "../../constants/theme";`, slice(194, 259)],
  ["components/mockups/BookingMock.jsx", `import React from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { Ic } from "../icons/Icon";`, slice(260, 287)],
  [
    "components/layout/Nav.jsx",
    `import React, { useState, useEffect } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { brandLogoLight } from "../../constants/brand";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Btn } from "../ui";`,
    slice(290, 432),
  ],
  [
    "components/layout/Footer.jsx",
    `import React from "react";\nimport { I } from "../icons/iconSet";\nimport { C, F } from "../../constants/theme";\nimport { brandLogoDark } from "../../constants/brand";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Chip } from "../ui";`,
    slice(435, 485),
  ],
  [
    "pages/Home.jsx",
    `import React, { useState } from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, H3, Body, Btn, Card, Divider } from "../components/ui";\nimport { DashMock } from "../components/mockups/DashMock";\nimport { BookingMock } from "../components/mockups/BookingMock";`,
    slice(488, 876),
  ],
  [
    "components/shared/marketingBlocks.jsx",
    `import React, { useState } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, Div } from "../ui";`,
    slice(879, 1312),
  ],
  [
    "components/layout/FeaturePageShell.jsx",
    `import React from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Label, H1, H2, H3, Body, Btn } from "../ui";`,
    slice(1314, 1389),
  ],
  ["pages/features/AppointmentsPage.jsx", featureImports, slice(1394, 1616)],
  ["pages/features/POSPage.jsx", featureImports, slice(1621, 1715)],
  ["pages/features/ClientsPage.jsx", featureImports, slice(1716, 1789)],
  ["pages/features/StaffPage.jsx", featureImports, slice(1790, 1859)],
  ["pages/features/RetailPage.jsx", featureImports, slice(1860, 1912)],
  ["pages/features/MarketingPage.jsx", featureImports, slice(1913, 1980)],
  ["pages/features/ReviewsPage.jsx", featureImports, slice(1981, 2023)],
  ["pages/features/AnalyticsPage.jsx", featureImports, slice(2024, 2124)],
  ["pages/features/MultiLocationPage.jsx", featureImports, slice(2125, 2191)],
  ["pages/features/WebsitePage.jsx", featureImports, slice(2192, 2313)],
  [
    "pages/BizType.jsx",
    `import React, { useState } from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";\nimport { DashMock } from "../components/mockups/DashMock";\nimport {\n  CalendarMock, StaffMock, ClientMock, AnalyticsMock, WebsiteMock,\n  PainGain, WorkflowStep,\n} from "../components/shared/marketingBlocks";`,
    slice(2318, 3192),
  ],
  ["pages/HowItWorks.jsx", pageImports, slice(3198, 3463)],
  ["pages/HelpCentre.jsx", pageImports, slice(3464, 3685)],
  ["pages/GettingStarted.jsx", pageImports, slice(3686, 3857)],
  ["pages/Blog.jsx", pageImports, slice(3859, 4024)],
  ["pages/About.jsx", pageImports, slice(4026, 4153)],
  ["pages/Contact.jsx", pageImports, slice(4155, 4311)],
  [
    "pages/auth/AppAuthRedirect.jsx",
    `import { useEffect } from "react";\nimport { C } from "../../constants/theme";\nimport { Body } from "../../components/ui";\nimport { goToAppAuth } from "../../constants/appAuth";`,
    slice(4316, 4323),
  ],
  [
    "pages/auth/Signup.jsx",
    `import { AppAuthRedirect } from "./AppAuthRedirect";`,
    slice(4325, 4327),
  ],
  [
    "pages/auth/Login.jsx",
    `import { AppAuthRedirect } from "./AppAuthRedirect";`,
    slice(4329, 4331),
  ],
  [
    "pages/Pricing.jsx",
    `import React, { useState } from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, Body, Btn, Divider } from "../components/ui";`,
    slice(4334, 4423),
  ],
  [
    "pages/SimplePage.jsx",
    `import React from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Label, H1, Body, Btn, Card } from "../components/ui";`,
    slice(4426, 4466),
  ],
  [
    "pages/FeaturesOverview.jsx",
    `import React from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, Btn } from "../components/ui";`,
    slice(4469, 4539),
  ],
];

for (const [rel, imports, body] of chunks) {
  write(rel, imports, body);
}

// App.jsx uses React Router — not synced from velour.jsx (see src/constants/routes.js)
console.log("  App.jsx (skipped — React Router shell)");

// Remove legacy data file not in velour.jsx
const legacy = path.join(OUT, "data/pricingPlans.js");
if (fs.existsSync(legacy)) {
  fs.unlinkSync(legacy);
  console.log("  (removed src/data/pricingPlans.js — plans live in velour.jsx Pricing + Home)");
}

console.log("\nDone. velour.jsx is the source of truth.");
