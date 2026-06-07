import React, { cloneElement } from "react";
import { I } from "./iconSet";

export const Ic = ({ n, sz = 20, col }) => {
  const icon = I[n];
  if (!icon) return null;
  const color = col || "currentColor";
  const filled = icon.props?.fill === "currentColor";
  return (
    <span style={{ display:"inline-flex", alignItems:"center", justifyContent:"center", width:sz, height:sz, minWidth:sz, minHeight:sz, flexShrink:0, color, lineHeight:0 }} aria-hidden="true">
      {cloneElement(icon, {
        width: sz,
        height: sz,
        color,
        ...(filled ? { fill: color } : { stroke: color }),
        style: { display: "block", width: sz, height: sz, flexShrink: 0 },
      })}
    </span>
  );
};
