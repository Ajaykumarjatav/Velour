import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, Div } from "../ui";

export const StatBadge = ({ value, label, good }) => (
  <div style={{ background:good ? C.tealLight : C.errBg, borderRadius:R, padding:"14px 18px", border:`1px solid ${good ? C.tealBorder : C.errBorder}`, textAlign:"center" }}>
    <div style={{ fontFamily:F, fontSize:24, fontWeight:800, color:good ? C.teal : C.err, lineHeight:1 }}>{value}</div>
    <div style={{ fontFamily:F, fontSize:12, color:good ? C.teal : C.err, marginTop:5, fontWeight:500 }}>{label}</div>
  </div>
);

/* ─── REUSABLE SHARED COMPONENTS ────────────────────────── */

/* Workflow step component */
export const WorkflowStep = ({ n, title, desc, note, final }) => (
  <div style={{ display:"flex", gap:16, alignItems:"flex-start" }}>
    <div style={{ display:"flex", flexDirection:"column", alignItems:"center", flexShrink:0 }}>
      <div style={{ width:36, height:36, borderRadius:"50%", background:final ? C.teal : C.tealLight, border:`2px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", fontFamily:F, fontSize:13, fontWeight:800, color:final ? "#fff" : C.teal }}>
        {final ? <Ic n="check" sz={16} col="#fff" /> : n}
      </div>
      {!final && <div style={{ width:2, height:32, background:C.tealLight, marginTop:4 }} />}
    </div>
    <div style={{ paddingTop:6, paddingBottom:final ? 0 : 16 }}>
      <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:4 }}>{title}</div>
      <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{desc}</div>
      {note && <div style={{ marginTop:6 }}><Chip teal sm>{note}</Chip></div>}
    </div>
  </div>
);

/* Pain vs gain comparison */
export function PainGain({ pains, gains }) {
  const { sm } = useW();
  return (
    <div style={{ borderRadius:R, overflow:"hidden", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
      {/* Header row */}
      <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
        <div style={{ background:"#fef2f2", padding:"16px 24px", display:"flex", alignItems:"center", gap:10, borderBottom:`2px solid #fecaca`, borderRight:sm ? "none" : `1px solid ${C.outlineVar}` }}>
          <div style={{ width:28, height:28, borderRadius:"50%", background:"#fee2e2", border:"1.5px solid #fca5a5", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
            <Ic n="xmark" sz={13} col="#dc2626" />
          </div>
          <div>
            <div style={{ fontFamily:F, fontSize:12, fontWeight:800, letterSpacing:"0.06em", color:"#dc2626", textTransform:"uppercase" }}>Without EasyGrox</div>
            <div style={{ fontFamily:F, fontSize:11.5, color:"#b91c1c", marginTop:1 }}>How most salons operate today</div>
          </div>
        </div>
        <div style={{ background:C.tealLight, padding:"16px 24px", display:"flex", alignItems:"center", gap:10, borderBottom:`2px solid ${C.tealBorder}`, borderTop:sm ? `1px solid ${C.outlineVar}` : "none" }}>
          <div style={{ width:28, height:28, borderRadius:"50%", background:"rgba(0,101,101,0.15)", border:`1.5px solid ${C.teal}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
            <Ic n="check" sz={13} col={C.teal} />
          </div>
          <div>
            <div style={{ fontFamily:F, fontSize:12, fontWeight:800, letterSpacing:"0.06em", color:C.teal, textTransform:"uppercase" }}>With EasyGrox</div>
            <div style={{ fontFamily:F, fontSize:11.5, color:C.tealDark, marginTop:1 }}>How your business operates on EasyGrox</div>
          </div>
        </div>
      </div>

      {/* Paired rows */}
      {pains.map((pain, i) => (
        <div key={i} style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
          {/* Pain side */}
          <div style={{ background:i % 2 === 0 ? "#fff9f9" : "#fef2f2", padding:"18px 24px", display:"flex", gap:12, alignItems:"flex-start", borderBottom:`1px solid #fee2e2`, borderRight:sm ? "none" : `1px solid ${C.outlineVar}`, borderTop:sm ? "none" : "none" }}>
            <div style={{ width:22, height:22, borderRadius:6, background:"#fee2e2", border:"1px solid #fca5a5", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
              <span style={{ fontFamily:F, fontSize:10, fontWeight:800, color:"#dc2626" }}>{i + 1}</span>
            </div>
            <span style={{ fontFamily:F, fontSize:13.5, color:"#7f1d1d", lineHeight:1.62, fontWeight:400 }}>{pain}</span>
          </div>
          {/* Gain side */}
          <div style={{ background:i % 2 === 0 ? C.surface : C.tealLight, padding:"18px 24px", display:"flex", gap:12, alignItems:"flex-start", borderBottom:`1px solid ${C.tealBorder}`, borderTop:sm ? `1px solid ${C.outlineVar}` : "none" }}>
            <div style={{ width:22, height:22, borderRadius:6, background:C.teal, border:`1px solid ${C.tealDark}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
              <Ic n="check" sz={11} col="#fff" />
            </div>
            <span style={{ fontFamily:F, fontSize:13.5, color:C.tealDark, lineHeight:1.62, fontWeight:500 }}>{gains[i] || ""}</span>
          </div>
        </div>
      ))}

      {/* Footer CTA strip */}
      <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
        <div style={{ background:"#fef2f2", padding:"14px 24px", display:"flex", alignItems:"center", gap:8, borderTop:"1px solid #fecaca" }}>
          <Ic n="xmark" sz={13} col="#dc2626" />
          <span style={{ fontFamily:F, fontSize:12.5, color:"#b91c1c", fontWeight:500 }}>Manual processes, missed opportunities, daily stress</span>
        </div>
        <div style={{ background:C.teal, padding:"14px 24px", display:"flex", alignItems:"center", gap:8, borderTop:`1px solid ${C.tealDark}`, borderLeft:sm ? "none" : `1px solid ${C.tealDark}` }}>
          <Ic n="check" sz={13} col="#fff" />
          <span style={{ fontFamily:F, fontSize:12.5, color:"rgba(255,255,255,0.9)", fontWeight:500 }}>One platform. Everything connected. Operations on autopilot.</span>
        </div>
      </div>
    </div>
  );
}

/* Scenario card */
export const Scenario = ({ emoji, title, desc, outcome }) => (
  <Card style={{ padding:"22px 20px" }}>
    <div style={{ fontSize:28, marginBottom:12, lineHeight:1 }}>{emoji}</div>
    <H4 style={{ marginBottom:6 }}>{title}</H4>
    <Body sm muted style={{ marginBottom:10, lineHeight:1.6 }}>{desc}</Body>
    <div style={{ display:"flex", alignItems:"flex-start", gap:7, background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:8, padding:"8px 12px" }}>
      <Ic n="zap" sz={13} col={C.teal} style={{ marginTop:1, flexShrink:0 }} />
      <span style={{ fontFamily:F, fontSize:12.5, color:C.tealDark, fontWeight:500, lineHeight:1.5 }}>{outcome}</span>
    </div>
  </Card>
);

/* Testimonial */
export const Quote = ({ text, name, role, metric }) => (
  <div style={{ background:C.surface, borderRadius:R, padding:"24px 22px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
    <div style={{ display:"flex", gap:1, marginBottom:14 }}>
      {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={13} col={C.teal} />)}
    </div>
    {metric && (
      <div style={{ background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:8, padding:"8px 14px", marginBottom:14, display:"inline-block" }}>
        <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal }}>{metric}</span>
      </div>
    )}
    <p style={{ fontFamily:F, fontSize:14.5, lineHeight:1.78, fontStyle:"italic", color:C.inkVar, marginBottom:18 }}>"{text}"</p>
    <Div style={{ marginBottom:14 }} />
    <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{name}</div>
    <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, marginTop:2 }}>{role}</div>
  </div>
);

/* Mini calendar mockup */
export const CalendarMock = () => {
  const slots = [
    { time:"10:00", name:"Ananya S.", svc:"Hair Colour + Cut", staff:"Priya", w:2, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"10:00", name:"", svc:"", staff:"Raj", w:1, color:"#f0f4f3", border:C.outlineVar, empty:true },
    { time:"10:00", name:"Walk-in", svc:"Haircut", staff:"Sunita", w:1, color:"rgba(146,64,14,0.1)", border:"#d97706" },
    { time:"11:00", name:"Ritu M.", svc:"Keratin Treatment", staff:"Priya", w:1, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"11:00", name:"Meera K.", svc:"Blowdry Styling", staff:"Raj", w:1, color:"rgba(0,101,101,0.12)", border:C.tealBorder },
    { time:"11:00", name:"Shreya P.", svc:"Hair Colour", staff:"Sunita", w:1, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"12:00", name:"Lunch Break", svc:"", staff:"Priya", w:1, color:"#f4f4f4", border:"#d1d5db", empty:true },
    { time:"12:00", name:"Pooja N.", svc:"Haircut", staff:"Raj", w:1, color:"rgba(0,101,101,0.12)", border:C.tealBorder },
    { time:"12:00", name:"", svc:"Available", staff:"Sunita", w:1, color:"#f9fef9", border:"#bbf7d0", empty:true },
  ];

  return (
    <div style={{ background:C.surface, borderRadius:R, overflow:"hidden", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, fontFamily:F }}>
      <div style={{ background:C.teal, padding:"12px 16px", display:"flex", justifyContent:"space-between", alignItems:"center" }}>
        <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Wednesday, 21 May 2026</div>
        <div style={{ display:"flex", gap:6 }}>
          {["Day","Week","Month"].map((v, i) => (
            <span key={i} style={{ fontSize:10.5, padding:"3px 9px", borderRadius:6, background:i === 0 ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.08)", color:"#fff", fontWeight:i === 0 ? 700 : 400 }}>{v}</span>
          ))}
        </div>
      </div>
      <div style={{ padding:"12px 14px" }}>
        <div style={{ display:"grid", gridTemplateColumns:"48px 1fr 1fr 1fr", gap:6, marginBottom:8 }}>
          <div />
          {["Priya","Raj","Sunita"].map((s, i) => (
            <div key={i} style={{ background:C.low, borderRadius:7, padding:"6px", textAlign:"center" }}>
              <div style={{ width:22, height:22, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9.5, fontWeight:800, color:"#fff", margin:"0 auto 3px" }}>{s[0]}</div>
              <div style={{ fontSize:10, color:C.inkVar, fontWeight:600 }}>{s}</div>
            </div>
          ))}
        </div>
        {[
          { time:"10:00", row:[slots[0], slots[1], slots[2]] },
          { time:"11:00", row:[slots[3], slots[4], slots[5]] },
          { time:"12:00", row:[slots[6], slots[7], slots[8]] },
        ].map((timeRow, ri) => (
          <div key={ri} style={{ display:"grid", gridTemplateColumns:"48px 1fr 1fr 1fr", gap:6, marginBottom:6 }}>
            <div style={{ fontSize:10, color:C.inkSoft, paddingTop:10, textAlign:"right", paddingRight:6 }}>{timeRow.time}</div>
            {timeRow.row.map((slot, si) => (
              <div key={si} style={{ background:slot.color, borderRadius:6, padding:"6px 8px", borderLeft:`2px solid ${slot.border}`, minHeight:44 }}>
                {!slot.empty ? (
                  <>
                    <div style={{ fontSize:10, fontWeight:700, color:C.ink, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{slot.name}</div>
                    <div style={{ fontSize:9.5, color:C.inkVar, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{slot.svc}</div>
                  </>
                ) : (
                  <div style={{ fontSize:9.5, color:C.inkSoft, fontStyle:"italic" }}>{slot.svc || "Blocked"}</div>
                )}
              </div>
            ))}
          </div>
        ))}
        <div style={{ marginTop:10, padding:"8px 12px", background:C.tealLight, borderRadius:8, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
          <span style={{ fontSize:11, color:C.teal, fontWeight:600 }}>8 of 12 slots filled today</span>
          <span style={{ fontSize:11, color:C.teal }}>3 online bookings · 1 walk-in</span>
        </div>
      </div>
    </div>
  );
};

/* POS Mockup */
export const POSMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/billing · Ananya Sharma</span>
    </div>
    <div style={{ padding:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
      <div>
        <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, fontWeight:600, marginBottom:10 }}>SERVICES & RETAIL</div>
        {[
          { item:"Hair Colour + Highlights", type:"Service", price:"₹1,200", staff:"Priya" },
          { item:"Blowdry Finish",           type:"Service", price:"₹300",   staff:"Priya" },
          { item:"Kerastase Mask 200ml",     type:"Retail",  price:"₹850",   staff:"—" },
        ].map((row, i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", padding:"7px 0", borderBottom:"1px solid rgba(255,255,255,0.06)" }}>
            <div>
              <div style={{ color:"#fff", fontSize:10.5, fontWeight:600 }}>{row.item}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5 }}>{row.type} {row.staff !== "—" ? `· ${row.staff}` : ""}</div>
            </div>
            <div style={{ color:"#76d6d5", fontSize:10.5, fontWeight:700 }}>{row.price}</div>
          </div>
        ))}
        <div style={{ paddingTop:10 }}>
          <div style={{ display:"flex", justifyContent:"space-between", fontSize:9, color:"rgba(255,255,255,0.4)", marginBottom:4 }}>
            <span>Subtotal</span><span>₹2,350</span>
          </div>
          <div style={{ display:"flex", justifyContent:"space-between", fontSize:9, color:"#f87171", marginBottom:6 }}>
            <span>Member Discount (10%)</span><span>-₹235</span>
          </div>
          <div style={{ display:"flex", justifyContent:"space-between", borderTop:"1px solid rgba(255,255,255,0.1)", paddingTop:7 }}>
            <span style={{ color:"#fff", fontSize:12, fontWeight:700 }}>Total</span>
            <span style={{ color:"#76d6d5", fontSize:14, fontWeight:800 }}>₹2,115</span>
          </div>
        </div>
      </div>
      <div>
        <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, fontWeight:600, marginBottom:10 }}>PAYMENT</div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:6, marginBottom:12 }}>
          {[{l:"UPI", a:true},{l:"Card"},{l:"Cash"},{l:"Wallet"}].map((m, i) => (
            <div key={i} style={{ background:m.a ? "rgba(0,101,101,0.5)" : "rgba(255,255,255,0.06)", borderRadius:7, padding:"8px", textAlign:"center", border:`1px solid ${m.a ? C.teal : "rgba(255,255,255,0.08)"}`, cursor:"pointer" }}>
              <div style={{ color:m.a ? "#76d6d5" : "rgba(255,255,255,0.45)", fontSize:10, fontWeight:600 }}>{m.l}</div>
            </div>
          ))}
        </div>
        <button style={{ width:"100%", padding:"11px", borderRadius:R, background:C.teal, border:"none", color:"#fff", fontSize:12, fontWeight:700, cursor:"pointer" }}>
          Collect ₹2,115
        </button>
        <div style={{ color:"rgba(255,255,255,0.3)", fontSize:9, textAlign:"center", marginTop:6 }}>Send receipt via WhatsApp · SMS · Email</div>
        <div style={{ marginTop:10, background:"rgba(255,255,255,0.04)", borderRadius:7, padding:"8px 10px" }}>
          <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, marginBottom:4 }}>Today so far</div>
          <div style={{ display:"flex", justifyContent:"space-between" }}>
            <span style={{ color:"#fff", fontSize:11, fontWeight:700 }}>₹14,200</span>
            <span style={{ color:"#76d6d5", fontSize:11 }}>9 transactions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
);

/* Analytics Mockup */
export const AnalyticsMock = () => {
  const bars = [38, 52, 44, 68, 55, 74, 100];
  return (
    <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
      <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
        <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/analytics</span>
      </div>
      <div style={{ padding:16 }}>
        <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
          <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Analytics Dashboard</div>
          <div style={{ display:"flex", gap:4 }}>
            {["Today","Week","Month"].map((p, i) => <span key={i} style={{ fontSize:9, padding:"2px 8px", borderRadius:9999, background:i === 2 ? "rgba(0,101,101,0.5)" : "rgba(255,255,255,0.07)", color:i === 2 ? "#76d6d5" : "rgba(255,255,255,0.4)" }}>{p}</span>)}
          </div>
        </div>
        <div style={{ background:"rgba(255,255,255,0.05)", borderRadius:8, padding:"12px 14px", marginBottom:12 }}>
          <div style={{ display:"flex", justifyContent:"space-between", marginBottom:10 }}>
            <div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9 }}>Monthly Revenue</div>
              <div style={{ color:"#fff", fontSize:22, fontWeight:800 }}>₹1,24,800</div>
              <div style={{ color:"#6bcb77", fontSize:9.5, marginTop:2 }}>+18.4% vs last month</div>
            </div>
            <div style={{ textAlign:"right" }}>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9 }}>Booking Conversion</div>
              <div style={{ color:"#76d6d5", fontSize:16, fontWeight:700 }}>18.3%</div>
            </div>
          </div>
          <div style={{ display:"flex", alignItems:"flex-end", gap:3, height:44 }}>
            {bars.map((h, i) => <div key={i} style={{ flex:1, borderRadius:"2px 2px 0 0", height:`${h}%`, background:i === 6 ? C.teal : "rgba(0,101,101,0.35)" }} />)}
          </div>
        </div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:8 }}>
          {[{l:"Appointments",v:"312",c:"#76d6d5"},{l:"Retention Rate",v:"74%",c:"#6bcb77"},{l:"No-show Rate",v:"4.1%",c:"#ffd93d"}].map((s, i) => (
            <div key={i} style={{ background:"rgba(255,255,255,0.06)", borderRadius:7, padding:"9px 10px" }}>
              <div style={{ color:s.c, fontSize:14, fontWeight:800 }}>{s.v}</div>
              <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, marginTop:2 }}>{s.l}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

/* Client Profile Mockup */
export const ClientMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/clients/ananya-sharma</span>
    </div>
    <div style={{ padding:16 }}>
      <div style={{ display:"flex", gap:12, alignItems:"center", marginBottom:14, paddingBottom:12, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ width:42, height:42, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:15, fontWeight:800, color:"#fff", flexShrink:0 }}>AS</div>
        <div style={{ flex:1 }}>
          <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Ananya Sharma</div>
          <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9.5 }}>Client since Jan 2024 · 28 visits</div>
        </div>
        <div style={{ background:"rgba(0,101,101,0.35)", borderRadius:9999, padding:"3px 10px" }}>
          <span style={{ color:"#76d6d5", fontSize:9, fontWeight:700 }}>VIP Client</span>
        </div>
      </div>
      <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:7, marginBottom:12 }}>
        {[{l:"Total Spent",v:"₹32,450"},{l:"Avg/Visit",v:"₹1,159"},{l:"Last Visit",v:"12 May"}].map((s, i) => (
          <div key={i} style={{ background:"rgba(255,255,255,0.06)", borderRadius:7, padding:"8px 9px", textAlign:"center" }}>
            <div style={{ color:"#76d6d5", fontSize:12, fontWeight:700 }}>{s.v}</div>
            <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8 }}>{s.l}</div>
          </div>
        ))}
      </div>
      <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"10px 12px", marginBottom:10 }}>
        <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, fontWeight:600, marginBottom:7 }}>COLOUR FORMULA NOTES</div>
        <div style={{ color:"rgba(255,255,255,0.7)", fontSize:11, lineHeight:1.6 }}>Wella 9/1 + 30vol. Highlights: Blondor Freelights. Processing: 35min. Toner: /81. Sensitivity: None. Preferred: Priya.</div>
      </div>
      <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, fontWeight:600, marginBottom:7 }}>RECENT VISITS</div>
      {[{d:"12 May",s:"Hair Colour + Cut",a:"₹1,500"},{d:"28 Apr",s:"Keratin Treatment",a:"₹2,800"}].map((v, i) => (
        <div key={i} style={{ display:"flex", justifyContent:"space-between", padding:"5px 0", borderBottom:i < 1 ? "1px solid rgba(255,255,255,0.06)" : "none" }}>
          <div>
            <div style={{ color:"#fff", fontSize:10 }}>{v.s}</div>
            <div style={{ color:"rgba(255,255,255,0.3)", fontSize:8.5 }}>{v.d}</div>
          </div>
          <div style={{ color:"#76d6d5", fontSize:10, fontWeight:700 }}>{v.a}</div>
        </div>
      ))}
    </div>
  </div>
);

/* Dashboard/Staff mockup */
export const StaffMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/staff</span>
    </div>
    <div style={{ padding:16 }}>
      <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
        <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Team Performance</div>
        <div style={{ background:"rgba(0,101,101,0.3)", borderRadius:9999, padding:"3px 10px", fontSize:9, color:"#76d6d5", fontWeight:600 }}>This Month</div>
      </div>
      {[
        { name:"Priya Sharma",  role:"Senior Stylist", appts:118, rev:"₹38,400", rating:4.9, util:91, col:C.teal },
        { name:"Raj Kumar",     role:"Stylist",        appts:96,  rev:"₹29,200", rating:4.7, util:78, col:"#8b4523" },
        { name:"Sunita Devi",   role:"Junior Stylist", appts:74,  rev:"₹21,800", rating:4.8, util:65, col:"#4a6741" },
      ].map((s, i) => (
        <div key={i} style={{ background:"rgba(255,255,255,0.05)", borderRadius:8, padding:"10px 12px", marginBottom:8 }}>
          <div style={{ display:"flex", alignItems:"center", gap:9, marginBottom:8 }}>
            <div style={{ width:30, height:30, borderRadius:"50%", background:s.col, display:"flex", alignItems:"center", justifyContent:"center", fontSize:11, fontWeight:800, color:"#fff", flexShrink:0 }}>{s.name.split(" ").map(n => n[0]).join("")}</div>
            <div style={{ flex:1 }}>
              <div style={{ color:"#fff", fontSize:11, fontWeight:600 }}>{s.name}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5 }}>{s.role}</div>
            </div>
            <div style={{ textAlign:"right" }}>
              <div style={{ color:"#76d6d5", fontSize:10.5, fontWeight:700 }}>{s.rev}</div>
              <div style={{ color:"#ffd93d", fontSize:9 }}>★ {s.rating}</div>
            </div>
          </div>
          <div style={{ display:"flex", gap:8, alignItems:"center" }}>
            <div style={{ flex:1, background:"rgba(255,255,255,0.08)", borderRadius:9999, height:4, overflow:"hidden" }}>
              <div style={{ width:`${s.util}%`, height:"100%", background:C.teal, borderRadius:9999 }} />
            </div>
            <span style={{ color:"rgba(255,255,255,0.4)", fontSize:8.5, flexShrink:0 }}>{s.util}% · {s.appts} appts</span>
          </div>
        </div>
      ))}
    </div>
  </div>
);

/* Booking website mockup */
export const WebsiteMock = () => (
  <div style={{ background:C.surface, borderRadius:R, overflow:"hidden", boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, fontFamily:F }}>
    <div style={{ background:C.teal, padding:"10px 16px", display:"flex", alignItems:"center", gap:8 }}>
      <div style={{ display:"flex", gap:4 }}>{[1,2,3].map(i => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:"rgba(255,255,255,0.2)" }} />)}</div>
      <div style={{ flex:1, background:"rgba(255,255,255,0.15)", borderRadius:6, height:17, display:"flex", alignItems:"center", padding:"0 9px", gap:5 }}>
        <Ic n="lock" sz={9} col="rgba(255,255,255,0.7)" />
        <span style={{ fontSize:9, color:"rgba(255,255,255,0.85)", fontFamily:"monospace" }}>luxehair.easygrox.com</span>
      </div>
    </div>
    <div style={{ padding:14 }}>
      <div style={{ textAlign:"center", marginBottom:14, paddingBottom:12, borderBottom:`1px solid ${C.outlineVar}` }}>
        <div style={{ width:36, height:36, borderRadius:"50%", background:C.tealLight, border:`2px solid ${C.teal}`, margin:"0 auto 8px", display:"flex", alignItems:"center", justifyContent:"center" }}>
          <Ic n="scissors" sz={16} col={C.teal} />
        </div>
        <div style={{ fontSize:13, fontWeight:800, color:C.ink }}>Luxe Hair Studio</div>
        <div style={{ fontSize:9.5, color:C.inkVar, margin:"2px 0" }}>Jaipur · Open until 8 PM · 4.9 stars</div>
        <div style={{ display:"flex", gap:5, justifyContent:"center", marginTop:8, flexWrap:"wrap" }}>
          {["Hair","Colour","Keratin","Bridal"].map((t, i) => <Chip key={i} teal sm>{t}</Chip>)}
        </div>
      </div>
      <div style={{ background:C.teal, borderRadius:R, padding:"9px", textAlign:"center", color:"#fff", fontSize:11, fontWeight:700, marginBottom:12, cursor:"pointer" }}>
        Book Appointment
      </div>
      <div style={{ marginBottom:12 }}>
        <div style={{ fontSize:10, fontWeight:700, color:C.ink, marginBottom:8 }}>Popular Services</div>
        {[
          { name:"Haircut & Styling",    dur:"45 min", price:"₹500", avail:"Today" },
          { name:"Hair Colour",          dur:"90 min", price:"₹1,200", avail:"Tomorrow" },
          { name:"Keratin Treatment",    dur:"2 hrs",  price:"₹2,800", avail:"Thu" },
        ].map((s, i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"6px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
            <div>
              <div style={{ fontSize:10.5, fontWeight:600, color:C.ink }}>{s.name}</div>
              <div style={{ fontSize:9, color:C.inkVar }}>{s.dur} · Next: {s.avail}</div>
            </div>
            <div style={{ display:"flex", alignItems:"center", gap:7 }}>
              <span style={{ fontSize:10.5, fontWeight:700, color:C.teal }}>{s.price}</span>
              <span style={{ background:C.tealLight, borderRadius:6, padding:"2px 7px", fontSize:8.5, color:C.teal, fontWeight:600 }}>Book</span>
            </div>
          </div>
        ))}
      </div>
      <div style={{ background:C.low, borderRadius:R, padding:"10px 12px" }}>
        <div style={{ fontSize:10, fontWeight:700, color:C.ink, marginBottom:8 }}>Choose Your Stylist</div>
        <div style={{ display:"flex", gap:8 }}>
          {[{n:"Priya",r:"Senior Stylist",a:"Next: 10am"},{n:"Raj",r:"Stylist",a:"Next: 12pm"},{n:"Sunita",r:"Junior",a:"Available"}].map((s, i) => (
            <div key={i} style={{ flex:1, background:C.surface, borderRadius:8, padding:"8px 6px", textAlign:"center", border:`1px solid ${C.outlineVar}`, cursor:"pointer" }}>
              <div style={{ width:24, height:24, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9, fontWeight:800, color:"#fff", margin:"0 auto 4px" }}>{s.n[0]}</div>
              <div style={{ fontSize:9, color:C.ink, fontWeight:600 }}>{s.n}</div>
              <div style={{ fontSize:8, color:C.teal }}>{s.a}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  </div>
);


