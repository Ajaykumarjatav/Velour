import fs from "fs";
import path from "path";

const ROOT = path.resolve(import.meta.dirname, "..");
const lines = fs.readFileSync(path.join(ROOT, "velour.jsx"), "utf8").split("\n");

const imports = `import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../../components/ui";
import { FeaturePageShell } from "../../components/layout/FeaturePageShell";
import {
  StatBadge, WorkflowStep, PainGain, Scenario, Quote,
  CalendarMock, POSMock, AnalyticsMock, ClientMock, StaffMock, WebsiteMock,
} from "../../components/shared/marketingBlocks";`;

const chunks = [
  ["src/pages/features/AppointmentsPage.jsx", 1371, 1593],
  ["src/pages/features/POSPage.jsx", 1598, 1688],
  ["src/pages/features/ClientsPage.jsx", 1693, 1762],
  ["src/pages/features/StaffPage.jsx", 1767, 1832],
  ["src/pages/features/RetailPage.jsx", 1837, 1885],
  ["src/pages/features/MarketingPage.jsx", 1890, 1953],
  ["src/pages/features/ReviewsPage.jsx", 1958, 1996],
  ["src/pages/features/AnalyticsPage.jsx", 2001, 2097],
  ["src/pages/features/MultiLocationPage.jsx", 2102, 2164],
  ["src/pages/features/WebsitePage.jsx", 2169, 2290],
];

function exportify(c) {
  return c
    .replace(/^function (\w+)/gm, "export function $1")
    .replace(/^const (\w+) = /gm, "export const $1 = ");
}

for (const [rel, s, e] of chunks) {
  const body = exportify(lines.slice(s - 1, e).join("\n"));
  fs.writeFileSync(path.join(ROOT, rel), `${imports}\n\n${body}\n`);
  console.log("fixed", rel);
}
