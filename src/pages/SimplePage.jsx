import React from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Label, H1, Body, Btn, Card } from "../components/ui";

export function SimplePage({ nav, label, title, body, items = [] }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };

  return (
    <>
      <section style={{ padding:sm ? "52px 0 40px" : "80px 0 56px", background:C.bg, textAlign:"center" }}>
        <div style={{ ...px, maxWidth:680, margin:"0 auto" }}>
          <Label center>{label}</Label>
          <H1 style={{ marginTop:12, marginBottom:16 }}>{title}</H1>
          <Body muted center style={{ fontSize:sm ? 16 : 17 }}>{body}</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap", marginTop:28 }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="outline" onClick={() => nav("howItWorks")}>How It Works</Btn>
          </div>
        </div>
      </section>
      <section style={{ padding:sm ? "44px 0" : "72px 0", background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {items.map((item, i) => (
              <Card key={i} tealTop style={{ padding:"22px 20px" }}>
                <div style={{ width:42, height:42, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", marginBottom:14, color:C.teal }}>
                  <Ic n={item.icon} sz={20} col={C.teal} />
                </div>
                <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:8 }}>{item.t}</div>
                <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{item.d}</div>
              </Card>
            ))}
          </div>
        </div>
      </section>
      <section style={{ padding:sm ? "52px 0" : "64px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <Body light center style={{ maxWidth:400, margin:"0 auto 24px" }}>Create your free account and publish your booking website today.</Body>
          <Btn onClick={() => nav("signup")}>Start for Free</Btn>
        </div>
      </section>
    </>
  );
}
