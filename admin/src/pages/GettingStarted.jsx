import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function GettingStarted({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [done, setDone] = useState({});
  const toggle = i => setDone(p => ({ ...p, [i]:!p[i] }));
  const completed = Object.values(done).filter(Boolean).length;

  const steps = [
    {
      phase:"01", icon:"lock", t:"Create your account", where:"This website · 30 seconds",
      tasks:["Go to easygrox.com and click Create Free Account","Enter your name, email, and a password","No credit card required — click Create Account","You are redirected to the EasyGrox app immediately"],
      tip:"Your account is created on this website. Everything after this happens inside the EasyGrox app.",
      milestone:"Account active",
    },
    {
      phase:"02", icon:"briefcase", t:"Set up your business profile", where:"App Settings · 3 minutes",
      tasks:["Open App Settings → Business Profile","Enter your business name and choose your category","Add your address, phone number, and WhatsApp","Upload your logo or profile photo","Save and continue"],
      tip:"Your business name and logo appear on your free booking website. Take 2 minutes to get this right.",
      milestone:"Profile complete",
    },
    {
      phase:"03", icon:"scissors", t:"Add your services", where:"App Settings · Services · 8–10 minutes",
      tasks:["Go to App Settings → Services → Add Service","Enter service name, category (e.g. Hair, Colour, Treatment)","Set the price and duration accurately","Repeat for every service you offer","Group related services into categories"],
      tip:"Service durations matter — they determine how much time is blocked in your calendar per appointment. Be accurate.",
      milestone:"Services menu ready",
    },
    {
      phase:"04", icon:"users", t:"Add your team members", where:"App Settings · Staff · 5 minutes",
      tasks:["Go to App Settings → Staff → Add Staff Member","Enter name, role, and contact details","Assign the services each staff member performs","Set their access level (Owner / Front Desk / Staff)","They will receive login credentials to their own dashboard"],
      tip:"Each staff member only needs to appear in booking slots for the services they are assigned to. This prevents incorrect bookings.",
      milestone:"Team onboarded",
    },
    {
      phase:"05", icon:"clock", t:"Set your opening hours", where:"App Settings · Hours · 3 minutes",
      tasks:["Go to App Settings → Opening Hours","Set open and close time for each day of the week","Toggle off days you are closed","Configure booking lead time (how far ahead clients can book)","Set a buffer time between appointments if needed"],
      tip:"Clients can only book during your configured opening hours. Setting this accurately prevents off-hours bookings.",
      milestone:"Hours configured",
    },
    {
      phase:"06", icon:"globe", t:"Preview and publish your booking website", where:"Website → Publish · 1 minute",
      tasks:["Open the Website tab inside the app","Preview your auto-generated booking website","Verify services, staff, hours, and contact details are correct","Choose your custom URL (yoursalon.easygrox.com)","Click Publish — your website is live instantly"],
      tip:"Share your booking link on Instagram bio, WhatsApp status, and Google Business Profile immediately after publishing.",
      milestone:"Website live",
    },
    {
      phase:"07", icon:"calendar", t:"Accept your first booking", where:"From your website or manually",
      tasks:["Share your booking link with a few existing clients","Or manually add an appointment from the calendar tab","When a client books online, it appears in your calendar instantly","You and the client both get a confirmation notification","Your first booking unlocks the full daily operations dashboard"],
      tip:"Your first booking is a milestone. Take a screenshot — your business is now officially running on EasyGrox.",
      milestone:"First booking received",
    },
    {
      phase:"08", icon:"pos", t:"Send your first invoice", where:"After first completed appointment",
      tasks:["When the appointment is done, tap Complete","The billing screen opens pre-loaded with the service","Add any retail products sold alongside the service","Select payment method and tap Collect","Receipt is sent to the client via WhatsApp automatically"],
      tip:"After your first invoice, explore the Daily Summary in Analytics. Your financial tracking begins from this moment.",
      milestone:"First invoice sent",
    },
  ];

  const totalTasks = steps.reduce((acc, s) => acc + s.tasks.length, 0);
  const progress = Math.round((completed / steps.length) * 100);

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 32 : 64, alignItems:"center" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>Interactive setup guide</Chip>
              <H1 style={{ marginBottom:18, marginTop:10 }}>Your business is almost ready. Let us get it live.</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17, marginBottom:28, lineHeight:1.72 }}>Eight steps. Tick each one off as you complete it. Most businesses finish in under 30 minutes and accept their first booking on the same day.</Body>
              <Btn onClick={() => nav("signup")}>Create Free Account — Start Step 1</Btn>
            </div>
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, padding:"24px 22px" }}>
              <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:12 }}>
                <H4>Launch Checklist</H4>
                <Chip teal>{completed} of {steps.length} done</Chip>
              </div>
              <div style={{ background:C.low, borderRadius:9999, height:8, marginBottom:16, overflow:"hidden" }}>
                <div style={{ width:`${progress}%`, height:"100%", background:C.teal, borderRadius:9999, transition:"width .4s ease" }} />
              </div>
              <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, marginBottom:16 }}>
                {progress === 0 ? "Ready to start. Click the first step below." : progress < 50 ? "Good start — keep going." : progress < 100 ? "You are more than halfway there." : "All steps complete — your business is live!"}
              </div>
              {steps.map((s, i) => (
                <div key={i} onClick={() => toggle(i)} style={{ display:"flex", gap:10, alignItems:"center", padding:"9px 0", borderBottom:i < steps.length-1 ? `1px solid ${C.outlineVar}` : "none", cursor:"pointer" }}
                  onMouseEnter={e => e.currentTarget.style.opacity = "0.8"}
                  onMouseLeave={e => e.currentTarget.style.opacity = "1"}>
                  <div style={{ width:22, height:22, borderRadius:"50%", background:done[i] ? C.teal : C.surface, border:`2px solid ${done[i] ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, transition:"all .18s" }}>
                    {done[i] && <Ic n="check" sz={12} col="#fff" />}
                  </div>
                  <div style={{ flex:1 }}>
                    <span style={{ fontFamily:F, fontSize:13.5, color:done[i] ? C.teal : C.ink, fontWeight:done[i] ? 600 : 400, textDecoration:done[i] ? "line-through" : "none" }}>{s.t}</span>
                  </div>
                  <span style={{ fontFamily:F, fontSize:11, color:C.inkSoft }}>{s.phase}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ── STEP DETAIL CARDS ── */}
      <Divider />
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Every step explained</Label>
            <H2 center>Follow this guide inside the app</H2>
          </div>
          <div style={{ display:"flex", flexDirection:"column", gap:16 }}>
            {steps.map((s, i) => (
              <div key={i} style={{ background:done[i] ? C.tealLight : C.surface, borderRadius:R, border:`1px solid ${done[i] ? C.tealBorder : C.outlineVar}`, boxShadow:C.sh1, overflow:"hidden", transition:"all .2s" }}>
                <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "56px 1fr 1fr", gap:0 }}>
                  {/* Step number */}
                  <div style={{ background:done[i] ? C.teal : C.low, display:"flex", alignItems:"center", justifyContent:"center", padding:sm ? "16px" : "0", borderRight:sm ? "none" : `1px solid ${done[i] ? C.tealDark : C.outlineVar}`, borderBottom:sm ? `1px solid ${done[i] ? C.tealBorder : C.outlineVar}` : "none" }}>
                    <div style={{ fontFamily:F, fontSize:18, fontWeight:800, color:done[i] ? "#fff" : C.teal }}>{s.phase}</div>
                  </div>
                  {/* Tasks */}
                  <div style={{ padding:"22px 24px", borderRight:sm ? "none" : `1px solid ${done[i] ? C.tealBorder : C.outlineVar}` }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center", marginBottom:14 }}>
                      <div style={{ width:34, height:34, borderRadius:9, background:done[i] ? C.teal : C.tealLight, border:`1px solid ${done[i] ? C.tealDark : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                        <Ic n={s.icon} sz={16} col={done[i] ? "#fff" : C.teal} />
                      </div>
                      <div>
                        <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:done[i] ? C.teal : C.ink }}>{s.t}</div>
                        <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar }}>{s.where}</div>
                      </div>
                    </div>
                    {s.tasks.map((task, ti) => (
                      <div key={ti} style={{ display:"flex", gap:8, marginBottom:8, alignItems:"flex-start" }}>
                        <div style={{ width:18, height:18, borderRadius:5, background:done[i] ? C.teal : C.tealLight, border:`1px solid ${done[i] ? C.tealDark : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
                          <span style={{ fontFamily:F, fontSize:9.5, fontWeight:800, color:done[i] ? "#fff" : C.teal }}>{ti+1}</span>
                        </div>
                        <span style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.55 }}>{task}</span>
                      </div>
                    ))}
                  </div>
                  {/* Tip + milestone */}
                  <div style={{ padding:"22px 24px", display:"flex", flexDirection:"column", justifyContent:"space-between", gap:16 }}>
                    <div style={{ background:done[i] ? "rgba(0,101,101,0.08)" : C.low, borderRadius:R, padding:"14px 16px", border:`1px solid ${done[i] ? C.tealBorder : C.outlineVar}` }}>
                      <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em", marginBottom:6 }}>Pro tip</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.6 }}>{s.tip}</div>
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em", marginBottom:6 }}>Milestone unlocked</div>
                      <Chip teal>{s.milestone}</Chip>
                    </div>
                    <button onClick={() => toggle(i)} style={{ background:done[i] ? "rgba(0,101,101,0.1)" : C.teal, border:`1px solid ${done[i] ? C.tealBorder : "transparent"}`, borderRadius:R, padding:"10px 16px", color:done[i] ? C.teal : "#fff", fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", display:"flex", alignItems:"center", gap:7, justifyContent:"center", transition:"all .18s" }}>
                      {done[i] ? <><Ic n="check" sz={14} col={C.teal} />Step Complete</> : <>Mark as Done</>}
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "52px 0" : "80px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <H2 light center style={{ marginBottom:14 }}>Your business is {progress > 0 ? `${progress}% ready` : "waiting to launch"}</H2>
          <Body light center style={{ marginBottom:28, maxWidth:440, margin:"0 auto 28px" }}>Create your free account and follow this guide inside the app. Most businesses are live in under 30 minutes.</Body>
          <Btn onClick={() => nav("signup")}>Create Free Account — No Credit Card</Btn>
        </div>
      </section>
    </>
  );
}

