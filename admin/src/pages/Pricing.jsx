import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, Body, Btn, Divider } from "../components/ui";

export function Pricing({ nav }) {
  const { sm, md } = useW();
  const [annual, setAnnual] = useState(false);
  const [openFaq, setOpenFaq] = useState(null);
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const sec = { padding:sm ? "56px 0" : "88px 0" };

  const plans = [
    { plan:"Solo",    for:"Independent stylists", price:"₹799",  apr:"₹666",  feats:["1 store · 1 staff login","Appointment calendar & POS","Client management","Automated SMS reminders","Free hosted booking website","Website visitor analytics"], hi:false },
    { plan:"Business",for:"Salons with a team",   price:"₹1,999",apr:"₹1,666", feats:["Everything in Solo","Up to 20 staff members","Shift scheduling & leave","Retail inventory","Full analytics (6 dashboards)","SMS & email campaigns","Free website + full analytics"], hi:true },
    { plan:"Multi-Location",for:"Multiple branches",price:"₹3,999",apr:"₹3,332",feats:["Everything in Business","Unlimited branches","Website per branch","Consolidated analytics","Priority support"], hi:false },
  ];

  const faqs = [
    { q:"Is the booking website really free forever?", a:"Yes. Every plan includes a fully hosted, mobile-responsive booking website at zero extra cost." },
    { q:"Can I switch plans later?", a:"Upgrade or downgrade anytime from your account settings." },
    { q:"Is there a contract?", a:"No long-term contracts. Pay monthly or annually and cancel whenever you need." },
  ];

  return (
    <>
      <section style={{ ...sec, background:C.bg, paddingTop:sm ? "48px" : "72px" }}>
        <div style={{ ...px, textAlign:"center", maxWidth:640, margin:"0 auto" }}>
          <Label center>No hidden fees</Label>
          <H1 style={{ marginTop:12, marginBottom:16 }}>Simple pricing. Free website on every plan.</H1>
          <Body muted center>Every account includes a hosted booking website, visitor analytics, and unlimited online bookings.</Body>
        </div>
      </section>
      <section style={{ ...sec, background:C.bg, paddingTop:0 }}>
        <div style={{ ...px }}>
          <div style={{ display:"flex", justifyContent:"center", marginBottom:36 }}>
            <div style={{ display:"inline-flex", alignItems:"center", gap:12, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"6px 6px 6px 18px", boxShadow:C.sh1 }}>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 400 : 600, color:annual ? C.inkVar : C.ink }}>Monthly</span>
              <div onClick={() => setAnnual(!annual)} style={{ width:44, height:24, borderRadius:9999, background:annual ? C.teal : C.outlineVar, position:"relative", cursor:"pointer", transition:"background .2s" }}>
                <div style={{ width:18, height:18, borderRadius:"50%", background:"#fff", position:"absolute", top:3, left:annual ? 23 : 3, transition:"left .2s", boxShadow:"0 1px 4px rgba(0,0,0,0.15)" }} />
              </div>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 600 : 400, color:annual ? C.ink : C.inkVar }}>Annual</span>
              {annual && <Chip teal sm>Save 2 months</Chip>}
            </div>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {plans.map((p, i) => (
              <div key={i} style={{ background:p.hi ? C.teal : C.surface, borderRadius:R, padding:"30px 24px", border:`1px solid ${p.hi ? C.teal : C.outlineVar}`, boxShadow:p.hi ? "0 16px 40px rgba(0,101,101,0.28)" : C.sh1, transform:p.hi && !sm ? "scale(1.03)" : "none", position:"relative" }}>
                {p.hi && <div style={{ position:"absolute", top:-13, left:"50%", transform:"translateX(-50%)" }}><span style={{ background:C.surface, color:C.teal, fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 14px", borderRadius:9999, border:`1px solid ${C.tealBorder}`, whiteSpace:"nowrap", textTransform:"uppercase", letterSpacing:"0.06em" }}>Most Popular</span></div>}
                <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar, marginBottom:4 }}>{p.plan}</div>
                <div style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.5)" : C.inkVar, marginBottom:18 }}>{p.for}</div>
                <div style={{ display:"flex", alignItems:"baseline", gap:4, marginBottom:20 }}>
                  <span style={{ fontFamily:F, fontSize:34, fontWeight:800, color:p.hi ? "#fff" : C.ink }}>{annual ? p.apr : p.price}</span>
                  <span style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar }}>/mo</span>
                </div>
                <Divider style={{ background:p.hi ? "rgba(255,255,255,0.15)" : undefined, marginBottom:20 }} />
                {p.feats.map((f, fi) => (
                  <div key={fi} style={{ display:"flex", gap:9, marginBottom:11, alignItems:"flex-start" }}>
                    <Ic n="check" sz={13} col={p.hi ? "rgba(255,255,255,0.6)" : C.teal} />
                    <span style={{ fontFamily:F, fontSize:13.5, color:p.hi ? "rgba(255,255,255,0.8)" : C.inkVar }}>{f}</span>
                  </div>
                ))}
                <button type="button" onClick={() => nav(p.plan === "Multi-Location" ? "contact" : "signup")}
                  style={{ width:"100%", height:46, marginTop:20, borderRadius:R, border:`1px solid ${p.hi ? "rgba(255,255,255,0.2)" : C.teal}`, background:p.hi ? "rgba(255,255,255,0.12)" : C.teal, color:"#fff", fontWeight:600, fontSize:14, cursor:"pointer", fontFamily:F }}>
                  {p.plan === "Multi-Location" ? "Contact Us" : "Start Free"}
                </button>
              </div>
            ))}
          </div>
        </div>
      </section>
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px, maxWidth:720, margin:"0 auto" }}>
          <H2 center style={{ marginBottom:32 }}>Pricing questions</H2>
          {faqs.map((f, i) => (
            <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
              <button type="button" onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"16px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
              </button>
              {openFaq === i && <div style={{ paddingBottom:16, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.75 }}>{f.a}</div>}
            </div>
          ))}
        </div>
      </section>
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <H2 light center style={{ marginBottom:16 }}>Start free today</H2>
          <Body light center style={{ maxWidth:420, margin:"0 auto 28px" }}>No credit card required. Publish your booking website in under 30 minutes.</Body>
          <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
        </div>
      </section>
    </>
  );
}
