import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function HowItWorks({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "56px 0" : "88px 0";
  const [activePhase, setActivePhase] = useState(0);

  const phases = [
    {
      n:"01", icon:"lock", label:"This website", tag:"30 seconds",
      t:"Create your account", color:C.teal,
      b:"Just your name, email, and password right here. No credit card. No store details asked upfront. One click and you are inside the EasyGrox app, ready to set up your business.",
      steps:[
        {icon:"check", t:"Enter name and email", d:"No credit card required. No billing details at this stage."},
        {icon:"lock",  t:"Set your password",    d:"Minimum 6 characters. Your account is encrypted and secure from the start."},
        {icon:"zap",   t:"Redirected instantly",  d:"The moment you click Create Account, you land inside the EasyGrox app."},
      ],
      milestone:"Account created in 30 seconds",
    },
    {
      n:"02", icon:"settings", label:"Inside the app", tag:"15–20 minutes",
      t:"Set up your business", color:"#7c3aed",
      b:"The app greets you with a guided setup checklist and a progress tracker. Every step is short, clear, and immediately actionable. Most owners complete full setup in under 20 minutes.",
      steps:[
        {icon:"briefcase",t:"Add your business details",  d:"Business name, address, contact, logo, and category. Takes 2 minutes."},
        {icon:"scissors", t:"Add your services",          d:"Every service with name, price, and duration. These auto-populate your booking website."},
        {icon:"users",    t:"Add your team",              d:"Create a profile for each staff member. Set their services and access level."},
        {icon:"clock",    t:"Set your hours",             d:"Opening hours, booking lead time, and buffer time between appointments."},
      ],
      milestone:"Business ready to accept bookings",
    },
    {
      n:"03", icon:"globe", label:"One click", tag:"Under 1 minute",
      t:"Publish your free booking website", color:C.teal,
      b:"Your booking website is built automatically from your store data. Preview it inside the app, choose your custom URL, and click Publish. Your site is live — mobile-ready, Google-indexed, accepting bookings — instantly.",
      steps:[
        {icon:"eye",      t:"Preview your website",       d:"See exactly what your clients will see. Services, staff, prices — all ready."},
        {icon:"link",     t:"Choose your URL",            d:"yoursalon.easygrox.com — or connect your own domain with one setting."},
        {icon:"globe",    t:"Click Publish",              d:"Live in seconds. Hosted free. Accepting bookings 24/7 immediately."},
        {icon:"mail",     t:"Share your booking link",    d:"Add to Instagram bio, WhatsApp status, and Google Business Profile."},
      ],
      milestone:"First online booking received",
    },
    {
      n:"04", icon:"bar", label:"Every day", tag:"Ongoing",
      t:"Manage your business from one dashboard", color:"#7c3aed",
      b:"Your EasyGrox dashboard becomes your morning ritual. Check revenue, review appointments, see staff performance, read your website analytics, and run campaigns — all from the same screen you opened yesterday.",
      steps:[
        {icon:"calendar", t:"Calendar — all bookings",    d:"Online, walk-in, and manual appointments — all staff, one view."},
        {icon:"pos",      t:"POS — bill in 60 seconds",   d:"Service pre-filled, payment collected, receipt sent. Done."},
        {icon:"bar",      t:"Analytics — read in 60s",    d:"Revenue, staff, clients, website — six dashboards, one app."},
        {icon:"tag",      t:"Marketing — send in 3 mins", d:"SMS campaigns, birthday offers, re-engagement — from your client data."},
      ],
      milestone:"Business running on autopilot",
    },
  ];

  const milestones = [
    {icon:"lock",     t:"Account created",        d:"30 seconds",      done:true},
    {icon:"settings", t:"Business setup complete", d:"20 minutes",      done:false},
    {icon:"globe",    t:"Booking website live",    d:"1 click",         done:false},
    {icon:"calendar", t:"First booking received",  d:"Day 1",           done:false},
    {icon:"pos",      t:"First invoice sent",      d:"After first visit",done:false},
    {icon:"bar",      t:"Analytics dashboard live",d:"Automatic",       done:false},
  ];

  const faqs = [
    {q:"Do I need technical skills to set up EasyGrox?",        a:"None at all. The setup flow is a guided checklist — each step has a short explanation and takes 2–5 minutes. If you can fill out a form, you can set up EasyGrox. Most owners complete full setup without reading any documentation."},
    {q:"How long does setup actually take?",                  a:"Account creation takes 30 seconds on this website. Store setup inside the app — services, staff, hours, and booking preferences — takes 15–20 minutes for most businesses. Publishing your booking website takes under a minute. Total: under 30 minutes from zero to live."},
    {q:"Is the booking website really free — forever?",       a:"Yes. Every EasyGrox plan includes a fully hosted, mobile-ready booking website at zero extra cost. No domain purchase, no hosting fee, no renewals. It is live the moment you click Publish and stays free as long as you are a EasyGrox customer."},
    {q:"What happens after I publish my website?",            a:"Clients can immediately find your website, browse your services and staff, and book appointments online. Every booking appears instantly in your EasyGrox calendar. Confirmation SMS is sent to the client automatically. You do nothing manually."},
    {q:"Can I import my existing client list?",               a:"Yes. You can import client data from most salon software via CSV during your setup. Our support team assists with data migration from Phorest, Fresha, Vagaro, and most other platforms at no extra charge."},
  ];

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "56px 0 44px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 72, alignItems:"center" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>No technical skill needed</Chip>
              <H1 style={{ marginBottom:20, marginTop:14 }}>From signup to a live booking website in under 30 minutes</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17.5, marginBottom:32, lineHeight:1.72 }}>Four steps. Guided the entire way. Your business is live, your calendar is open, and your clients can book online — before lunch.</Body>
              <div style={{ display:"flex", gap:12, flexWrap:"wrap", marginBottom:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
                <Btn v="outline" onClick={() => nav("gettingStarted")}>View Setup Guide</Btn>
              </div>
              <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                {["30 sec signup","Free website","No credit card","Guided setup"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px" }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>
            {/* Progress card */}
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
              <div style={{ background:C.teal, padding:"16px 22px" }}>
                <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Your launch journey</div>
                <div style={{ color:"rgba(255,255,255,0.55)", fontSize:11, marginTop:2 }}>From zero to live booking business</div>
              </div>
              <div style={{ padding:"18px 22px" }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:8 }}>
                  <span style={{ fontFamily:F, fontSize:13, fontWeight:600, color:C.ink }}>Setup Progress</span>
                  <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal }}>Step 1 of 4</span>
                </div>
                <div style={{ background:C.low, borderRadius:9999, height:6, marginBottom:20, overflow:"hidden" }}>
                  <div style={{ width:"25%", height:"100%", background:C.teal, borderRadius:9999 }} />
                </div>
                {milestones.map((m, i) => (
                  <div key={i} style={{ display:"flex", gap:12, alignItems:"center", padding:"10px 0", borderBottom:i < milestones.length-1 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ width:32, height:32, borderRadius:"50%", background:m.done ? C.teal : C.surface, border:`2px solid ${m.done ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      {m.done ? <Ic n="check" sz={14} col="#fff" /> : <Ic n={m.icon} sz={13} col={C.inkSoft} />}
                    </div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:m.done ? 700 : 400, color:m.done ? C.teal : C.ink }}>{m.t}</div>
                      <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar }}>{m.d}</div>
                    </div>
                    {m.done ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:11, color:C.inkSoft }}>Pending</span>}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── PHASE TABS ── */}
      <Divider />
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Four phases. Fully guided.</Label>
            <H2 center>Every step explained — before you take it</H2>
          </div>
          {/* Phase selector */}
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : "1fr 1fr 1fr 1fr", gap:10, marginBottom:36 }}>
            {phases.map((ph, i) => (
              <button key={i} onClick={() => setActivePhase(i)}
                style={{ padding:sm ? "14px 12px" : "18px 16px", borderRadius:R, border:`2px solid ${activePhase===i ? C.teal : C.outlineVar}`, background:activePhase===i ? C.tealLight : C.surface, cursor:"pointer", textAlign:"left", transition:"all .18s", boxShadow:activePhase===i ? `0 4px 16px rgba(0,101,101,0.18)` : C.sh1 }}>
                <div style={{ display:"flex", alignItems:"center", gap:8, marginBottom:8 }}>
                  <div style={{ width:32, height:32, borderRadius:8, background:activePhase===i ? C.teal : C.low, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                    <Ic n={ph.icon} sz={15} col={activePhase===i ? "#fff" : C.inkSoft} />
                  </div>
                  <span style={{ fontFamily:F, fontSize:10, fontWeight:700, color:activePhase===i ? C.teal : C.inkSoft, letterSpacing:"0.06em", textTransform:"uppercase" }}>Phase {ph.n}</span>
                </div>
                <div style={{ fontFamily:F, fontSize:sm ? 13 : 14, fontWeight:700, color:activePhase===i ? C.teal : C.ink, marginBottom:3 }}>{ph.t}</div>
                <Chip teal={activePhase===i} sm style={{ marginTop:4 }}>{ph.tag}</Chip>
              </button>
            ))}
          </div>
          {/* Active phase detail */}
          <Card style={{ padding:sm ? "24px 20px" : "36px 40px", borderLeft:`4px solid ${C.teal}`, borderRadius:`0 ${R} ${R} 0` }}>
            <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 28 : 56, alignItems:"start" }}>
              <div>
                <div style={{ display:"flex", gap:10, alignItems:"center", marginBottom:16, flexWrap:"wrap" }}>
                  <span style={{ background:C.teal, color:"#fff", fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 12px", borderRadius:9999, textTransform:"uppercase", letterSpacing:"0.06em" }}>Phase {phases[activePhase].n}</span>
                  <Chip teal sm>{phases[activePhase].tag}</Chip>
                  <span style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>{phases[activePhase].label}</span>
                </div>
                <H2 style={{ marginBottom:14 }}>{phases[activePhase].t}</H2>
                <Body muted style={{ marginBottom:28, fontSize:15.5, lineHeight:1.75 }}>{phases[activePhase].b}</Body>
                <div style={{ display:"grid", gridTemplateColumns:"1fr", gap:12 }}>
                  {phases[activePhase].steps.map((s, si) => (
                    <div key={si} style={{ display:"flex", gap:12, alignItems:"flex-start", background:C.low, borderRadius:R, padding:"14px 16px" }}>
                      <div style={{ width:32, height:32, borderRadius:8, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        <Ic n={s.icon} sz={15} col={C.teal} />
                      </div>
                      <div>
                        <div style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.ink, marginBottom:3 }}>{s.t}</div>
                        <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55 }}>{s.d}</div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              <div>
                <div style={{ background:C.tealLight, border:`2px solid ${C.tealBorder}`, borderRadius:R, padding:"24px 22px", textAlign:"center" }}>
                  <div style={{ width:56, height:56, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 16px" }}>
                    <Ic n={phases[activePhase].icon} sz={24} col="#fff" />
                  </div>
                  <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, letterSpacing:"0.08em", textTransform:"uppercase", marginBottom:8 }}>Milestone</div>
                  <div style={{ fontFamily:F, fontSize:18, fontWeight:800, color:C.teal, marginBottom:12, lineHeight:1.3 }}>{phases[activePhase].milestone}</div>
                  <Divider style={{ marginBottom:16 }} />
                  <Body sm muted style={{ fontSize:13 }}>When you complete Phase {phases[activePhase].n}, this milestone is unlocked and your business moves to the next stage of readiness.</Body>
                </div>
                <div style={{ marginTop:16, display:"flex", gap:10, flexDirection:"column" }}>
                  {activePhase !== 3 && (
                    <button onClick={() => setActivePhase(activePhase + 1)} style={{ background:C.teal, border:"none", borderRadius:R, padding:"12px 20px", color:"#fff", fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", display:"flex", alignItems:"center", justifyContent:"space-between", gap:8 }}>
                      Next: Phase 0{activePhase + 2} — {phases[activePhase + 1].t}
                      <Ic n="chevR" sz={14} col="#fff" />
                    </button>
                  )}
                  {activePhase === 3 && (
                    <Btn full onClick={() => nav("signup")}>Create Free Account — Start Phase 01</Btn>
                  )}
                </div>
              </div>
            </div>
          </Card>
        </div>
      </section>

      {/* ── WHAT YOU CAN DO ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>After setup is complete</Label>
            <H2 center>Everything you can do from Day 1</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0" }}>Once your store is live, you have the full EasyGrox platform — every feature, connected, ready to use.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {n:"calendar",t:"Accept online bookings",        d:"Clients book through your free website — slots fill overnight while you sleep."},
              {n:"zap",     t:"Manage walk-ins in 10 seconds", d:"Add a walk-in, assign a staff member, and it is in the calendar instantly."},
              {n:"pos",     t:"Bill any client in 60 seconds", d:"Services and retail in one transaction. All payment types. WhatsApp receipt."},
              {n:"bell",    t:"Reminders go out automatically",d:"Confirmation and 24h reminder to every client — zero manual effort."},
              {n:"user",    t:"Build client profiles",         d:"Every visit, service, and product tracked per client. Automatically."},
              {n:"bar",     t:"Read your analytics daily",     d:"Revenue, staff, clients, and website — six dashboards, one morning read."},
              {n:"tag",     t:"Send marketing campaigns",       d:"SMS to lapsed clients, birthday offers, flash promos — all from your client data."},
              {n:"users",   t:"Manage your team",              d:"Shifts, leave, performance, and personal dashboards for every staff member."},
              {n:"globe",   t:"Your website stays current",    d:"Change a price inside EasyGrox — your booking website updates in seconds."},
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 18px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:7 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ── FAQ ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px, maxWidth:760, margin:"0 auto" }}>
          <H2 center style={{ marginBottom:36 }}>Questions about setup</H2>
          {faqs.map((f, i) => (
            <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
              <button onClick={() => setActivePhase(activePhase === i + 10 ? -1 : i + 10)} style={{ width:"100%", padding:"17px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                <Ic n={activePhase === i + 10 ? "minus" : "plus"} sz={14} col={C.teal} />
              </button>
              {activePhase === i + 10 && <div style={{ paddingBottom:18, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.78 }}>{f.a}</div>}
            </div>
          ))}
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "56px 0" : "88px 0", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Start Phase 01 right now</Label>
          <H2 light center style={{ maxWidth:540, margin:"12px auto 18px" }}>30 seconds to create your account. 30 minutes to go live.</H2>
          <Body light center style={{ maxWidth:440, margin:"0 auto 32px" }}>No credit card. No technical skill needed. Guided the entire way.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("gettingStarted")}>View Setup Guide First</Btn>
          </div>
        </div>
      </section>
    </>
  );
}

/* ─── HELP CENTRE PAGE ───────────────────────────────────── */
