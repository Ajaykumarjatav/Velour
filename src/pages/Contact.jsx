import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function Contact({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [formData, setFormData] = useState({ name:"", email:"", business:"", type:"", message:"" });
  const [category, setCategory] = useState("");
  const [sent, setSent] = useState(false);

  const categories = [
    {icon:"zap",      label:"Getting Started",      d:"I need help setting up my account or business profile."},
    {icon:"settings", label:"Technical Support",    d:"I am already a EasyGrox user with a question about a feature."},
    {icon:"globe",    label:"Booking Website Help", d:"Questions about my free booking website, URL, or analytics."},
    {icon:"users",    label:"Team Setup",           d:"Help adding staff, setting access levels, or managing shifts."},
    {icon:"bar",      label:"Reports and Billing",  d:"Questions about my analytics dashboard or subscription billing."},
    {icon:"briefcase",label:"Partnerships",         d:"Integration partnerships, reseller programs, or affiliates."},
  ];

  const contactOptions = [
    {icon:"mail",    t:"Email",      d:"support@easygrox.com", sub:"Response within 24 hours on business days"},
    {icon:"phone",   t:"WhatsApp",   d:"+91 98765 43210",    sub:"Quick support for active EasyGrox users"},
    {icon:"calendar",t:"Setup Call", d:"Book a free 20-minute guided setup call", sub:"For new users who want a walkthrough"},
  ];

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>No bots. No queues.</Chip>
              <H1 style={{ marginBottom:20, marginTop:10 }}>We are here. What do you need?</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17, marginBottom:36, lineHeight:1.72 }}>Whether you are setting up for the first time or have been running your business on EasyGrox for a year — our team responds personally within 24 hours.</Body>
              {/* Contact options */}
              <div style={{ display:"flex", flexDirection:"column", gap:12, marginBottom:32 }}>
                {contactOptions.map((c, i) => (
                  <div key={i} style={{ display:"flex", gap:14, alignItems:"flex-start", background:C.surface, borderRadius:R, padding:"16px 18px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ width:40, height:40, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={c.icon} sz={18} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:2 }}>{c.t}</div>
                      <div style={{ fontFamily:F, fontSize:14, color:C.teal, fontWeight:500, marginBottom:2 }}>{c.d}</div>
                      <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar }}>{c.sub}</div>
                    </div>
                  </div>
                ))}
              </div>
              {/* Trust signals */}
              <div style={{ display:"flex", gap:16, flexWrap:"wrap" }}>
                {["24h response","Real team members","No sales pressure","Free setup help"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>

            {/* FORM */}
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
              <div style={{ background:C.teal, padding:"18px 24px" }}>
                <div style={{ color:"#fff", fontSize:15, fontWeight:700 }}>Send us a message</div>
                <div style={{ color:"rgba(255,255,255,0.55)", fontSize:12, marginTop:2 }}>We respond within 24 hours — personally</div>
              </div>
              {sent ? (
                <div style={{ padding:"48px 32px", textAlign:"center" }}>
                  <div style={{ width:56, height:56, borderRadius:"50%", background:C.tealLight, border:`2px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 20px" }}>
                    <Ic n="check" sz={24} col={C.teal} />
                  </div>
                  <H3 style={{ marginBottom:10 }}>Message received</H3>
                  <Body muted style={{ marginBottom:24 }}>Our team will respond within 24 hours. If your question is urgent, WhatsApp us directly.</Body>
                  <Btn v="outline" onClick={() => setSent(false)}>Send another message</Btn>
                </div>
              ) : (
                <div style={{ padding:"24px 24px" }}>
                  {/* Category picker */}
                  <div style={{ marginBottom:20 }}>
                    <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:10 }}>What do you need help with?</label>
                    <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:8 }}>
                      {categories.map((c, i) => (
                        <button key={i} onClick={() => setCategory(c.label)}
                          style={{ padding:"10px 12px", borderRadius:R, border:`1.5px solid ${category===c.label ? C.teal : C.outlineVar}`, background:category===c.label ? C.tealLight : C.surface, cursor:"pointer", textAlign:"left", transition:"all .15s" }}>
                          <div style={{ display:"flex", alignItems:"center", gap:7, marginBottom:3 }}>
                            <Ic n={c.icon} sz={13} col={category===c.label ? C.teal : C.inkSoft} />
                            <span style={{ fontFamily:F, fontSize:12.5, fontWeight:700, color:category===c.label ? C.teal : C.ink }}>{c.label}</span>
                          </div>
                          <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar, lineHeight:1.4 }}>{c.d}</div>
                        </button>
                      ))}
                    </div>
                  </div>
                  {/* Form fields */}
                  {[
                    {key:"name",     label:"Your name",         ph:"Full name",              type:"text"},
                    {key:"email",    label:"Email address",      ph:"your@email.com",          type:"email"},
                    {key:"business", label:"Business name",      ph:"e.g. Luxe Hair Studio",  type:"text"},
                  ].map((field) => (
                    <div key={field.key} style={{ marginBottom:14 }}>
                      <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:6 }}>{field.label}</label>
                      <input value={formData[field.key]} onChange={e => setFormData(p => ({ ...p, [field.key]:e.target.value }))} placeholder={field.ph} type={field.type}
                        style={{ width:"100%", height:44, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"0 14px", fontSize:14.5, fontFamily:F, outline:"none", boxSizing:"border-box", background:C.bg }}
                        onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
                        onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
                    </div>
                  ))}
                  <div style={{ marginBottom:18 }}>
                    <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:6 }}>Your message</label>
                    <textarea value={formData.message} onChange={e => setFormData(p => ({ ...p, message:e.target.value }))} placeholder="Tell us exactly what you need help with. The more detail, the faster we can help."
                      style={{ width:"100%", height:100, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"12px 14px", fontSize:14.5, fontFamily:F, outline:"none", boxSizing:"border-box", resize:"vertical", background:C.bg, lineHeight:1.6 }}
                      onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
                      onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
                  </div>
                  <Btn full onClick={() => { if (formData.name && formData.email) setSent(true); }}>Send Message</Btn>
                  <Body sm muted style={{ marginTop:12, fontSize:12, textAlign:"center" }}>We respond personally within 24 hours. No bots, no automated replies.</Body>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* ── FAQ STRIP ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <H3 style={{ marginBottom:24, textAlign:"center" }}>Quick answers</H3>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {q:"Do I need to call anyone to sign up?",    a:"No. Signup is completely self-serve on this website. Create your account, set up your business in the app, and publish your free website — no sales call required."},
              {q:"How long does it take to get a response?",a:"Within 24 hours on business days. For urgent issues, WhatsApp us directly — active EasyGrox users get priority response."},
              {q:"Is there a free trial?",                  a:"There is no trial — instead, you create a free account and set up your business fully before any payment is needed. You see exactly what you are getting before committing."},
              {q:"Can I get help migrating from another platform?",a:"Yes. Our team helps with data migration from Phorest, Fresha, Vagaro, and most other platforms at no charge. Mention it in your message and we will coordinate."},
              {q:"Can I book a setup call?",                a:"Yes. New users can book a free 20-minute call with our onboarding team. We walk through your store setup, answer your questions, and help you publish your booking website."},
              {q:"Do you offer partnerships or white-label?",a:"We work with selected integration and reseller partners. If you are interested in a partnership arrangement, choose Partnerships from the contact form and our team will reach out."},
            ].map((f, i) => (
              <Card key={i} style={{ padding:"22px 20px" }}>
                <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{f.q}</div>
                <Body sm muted style={{ lineHeight:1.65 }}>{f.a}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "52px 0" : "80px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <H2 light center style={{ marginBottom:14 }}>Ready to set up your business?</H2>
          <Body light center style={{ maxWidth:440, margin:"0 auto 28px" }}>Create your free account and follow the guided setup. Most businesses are live in under 30 minutes — no help needed.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("howItWorks")}>See How It Works</Btn>
          </div>
        </div>
      </section>
    </>
  );
}
