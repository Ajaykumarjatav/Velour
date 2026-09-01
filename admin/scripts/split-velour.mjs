/**
 * Splits velour.jsx into modular src/ files.
 * Run: node scripts/split-velour.mjs
 */
import fs from "fs";
import path from "path";

const ROOT = path.resolve(import.meta.dirname, "..");
const SRC = path.join(ROOT, "velour.jsx");
const OUT = path.join(ROOT, "src");

const lines = fs.readFileSync(SRC, "utf8").split("\n");

function slice(start, end) {
  return lines.slice(start - 1, end).join("\n");
}

function exportify(code, { defaultFn } = {}) {
  let c = code.replace(/\r\n/g, "\n");
  if (defaultFn) {
    c = c.replace(
      new RegExp(`^function ${defaultFn}\\b`, "m"),
      `export default function ${defaultFn}`
    );
  }
  c = c.replace(/^function (\w+)/gm, "export function $1");
  c = c.replace(/^const (\w+) = /gm, "export const $1 = ");
  return c;
}

function write(rel, imports, body, opts) {
  const dir = path.dirname(path.join(OUT, rel));
  fs.mkdirSync(dir, { recursive: true });
  const content = `${imports}\n\n${exportify(body, opts)}\n`;
  fs.writeFileSync(path.join(OUT, rel), content, "utf8");
  console.log("wrote", rel);
}

const themeBody = slice(10, 19)
  .replace(/^const C = /m, "export const C = ")
  .replace(/^const F = /m, "export const F = ")
  .replace(/^const R = /m, "export const R = ");
fs.mkdirSync(path.join(OUT, "constants"), { recursive: true });
fs.writeFileSync(path.join(OUT, "constants/theme.js"), themeBody + "\n");

const chunks = [
  {
    rel: "hooks/useWindowWidth.js",
    imports: `import { useState, useEffect } from "react";`,
    body: slice(22, 30),
  },
  {
    rel: "components/icons/iconSet.jsx",
    imports: `import React from "react";`,
    body: slice(33, 82),
  },
  {
    rel: "components/icons/Icon.jsx",
    imports: `import React from "react";\nimport { I } from "./iconSet";`,
    body: slice(84, 88),
  },
  {
    rel: "components/ui/index.jsx",
    imports: `import React, { useState } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { Ic } from "../icons/Icon";`,
    body: slice(91, 158),
  },
  {
    rel: "components/mockups/DashMock.jsx",
    imports: `import React from "react";\nimport { C, F, R } from "../../constants/theme";`,
    body: slice(161, 225),
  },
  {
    rel: "components/mockups/BookingMock.jsx",
    imports: `import React from "react";\nimport { C, F, R } from "../../constants/theme";`,
    body: slice(227, 254),
  },
  {
    rel: "components/layout/Nav.jsx",
    imports: `import React, { useState, useEffect } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Btn } from "../ui";`,
    body: slice(257, 406),
  },
  {
    rel: "components/layout/Footer.jsx",
    imports: `import React from "react";\nimport { I } from "../icons/iconSet";\nimport { C, F } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Chip } from "../ui";`,
    body: slice(409, 462),
  },
  {
    rel: "pages/Home.jsx",
    imports: `import React, { useState } from "react";\nimport { C, F } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, Body, Btn, Card, Divider } from "../components/ui";\nimport { DashMock } from "../components/mockups/DashMock";\nimport { BookingMock } from "../components/mockups/BookingMock";`,
    body: slice(465, 853),
  },
  {
    rel: "components/shared/marketingBlocks.jsx",
    imports: `import React, { useState } from "react";\nimport { C, F, R } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Chip, Label, H2, H3, Body, Btn, Card, Divider } from "../ui";`,
    body: slice(856, 1287),
  },
  {
    rel: "components/layout/FeaturePageShell.jsx",
    imports: `import React from "react";\nimport { C } from "../../constants/theme";\nimport { useW } from "../../hooks/useWindowWidth";\nimport { Ic } from "../icons/Icon";\nimport { Label, H1, H2, Body, Btn } from "../ui";`,
    body: slice(1291, 1366),
  },
  {
    rel: "pages/features/AppointmentsPage.jsx",
    imports: featurePageImports(),
    body: slice(1371, 1593),
  },
  {
    rel: "pages/features/POSPage.jsx",
    imports: featurePageImports(),
    body: slice(1598, 1688),
  },
  {
    rel: "pages/features/ClientsPage.jsx",
    imports: featurePageImports(),
    body: slice(1693, 1762),
  },
  {
    rel: "pages/features/StaffPage.jsx",
    imports: featurePageImports(),
    body: slice(1767, 1832),
  },
  {
    rel: "pages/features/RetailPage.jsx",
    imports: featurePageImports(),
    body: slice(1837, 1885),
  },
  {
    rel: "pages/features/MarketingPage.jsx",
    imports: featurePageImports(),
    body: slice(1890, 1953),
  },
  {
    rel: "pages/features/ReviewsPage.jsx",
    imports: featurePageImports(),
    body: slice(1958, 1996),
  },
  {
    rel: "pages/features/AnalyticsPage.jsx",
    imports: featurePageImports(),
    body: slice(2001, 2097),
  },
  {
    rel: "pages/features/MultiLocationPage.jsx",
    imports: featurePageImports(),
    body: slice(2102, 2164),
  },
  {
    rel: "pages/features/WebsitePage.jsx",
    imports: featurePageImports(),
    body: slice(2169, 2290),
  },
  {
    rel: "pages/BizType.jsx",
    imports: `import React, { useState } from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, H3, Body, Btn, Card, Divider } from "../components/ui";\nimport { DashMock } from "../components/mockups/DashMock";`,
    body: slice(2295, 3169),
  },
  {
    rel: "pages/HowItWorks.jsx",
    imports: `import React, { useState } from "react";\nimport { C, F, R } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Label, H1, H2, H3, Body, Btn, Card, Divider } from "../components/ui";`,
    body: slice(3175, 3438),
  },
  {
    rel: "pages/HelpCentre.jsx",
    imports: pageImports(),
    body: slice(3441, 3660),
  },
  {
    rel: "pages/GettingStarted.jsx",
    imports: pageImports(),
    body: slice(3663, 3833),
  },
  {
    rel: "pages/Blog.jsx",
    imports: pageImports(),
    body: slice(3836, 4000),
  },
  {
    rel: "pages/About.jsx",
    imports: pageImports(),
    body: slice(4003, 4129),
  },
  {
    rel: "pages/Contact.jsx",
    imports: pageImports(),
    body: slice(4132, 4290),
  },
  {
    rel: "pages/FeaturesOverview.jsx",
    imports: `import React from "react";\nimport { C, F } from "../constants/theme";\nimport { useW } from "../hooks/useWindowWidth";\nimport { Ic } from "../components/icons/Icon";\nimport { Chip, Btn } from "../components/ui";`,
    body: slice(4293, 4363),
  },
];

function featurePageImports() {
  return `import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../../components/ui";
import { FeaturePageShell } from "../../components/layout/FeaturePageShell";
import {
  StatBadge, WorkflowStep, PainGain, Scenario, Quote,
  CalendarMock, POSMock, AnalyticsMock, ClientMock, StaffMock, WebsiteMock,
} from "../../components/shared/marketingBlocks";`;
}

function pageImports() {
  return `import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, Body, Btn, Card, Divider } from "../components/ui";`;
}

for (const ch of chunks) {
  write(ch.rel, ch.imports, ch.body, ch.opts);
}

console.log("Done. Add App, auth pages, package.json manually or via scaffold.");
