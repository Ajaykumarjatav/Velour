import React from "react";
import { C, F, R } from "../../constants/theme";

export function DashMock({ compact }) {
  const bars = [32, 48, 42, 64, 55, 72, 88];
  const appts = [
    { name:"Ananya S.", svc:"Hair Colour", time:"10:00", status:"confirmed" },
    { name:"Ritu M.",   svc:"Haircut",     time:"11:30", status:"active" },
    { name:"Meera K.",  svc:"Keratin",     time:"1:00",  status:"upcoming" },
  ];
  return (
    <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 24px 64px rgba(0,0,0,0.2)", fontFamily:F }}>
      <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ display:"flex", gap:4 }}>
          {["#ff6b6b","#ffd93d","#6bcb77"].map((cl,i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}
        </div>
        <div style={{ flex:1, background:"rgba(255,255,255,0.06)", borderRadius:5, height:17, display:"flex", alignItems:"center", padding:"0 9px" }}>
          <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace" }}>easygrox.com/dashboard</span>
        </div>
      </div>
      <div style={{ padding:compact ? 12 : 18 }}>
        <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
          <div>
            <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9.5, marginBottom:2 }}>Wednesday, 21 May 2026</div>
            <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Good morning, Priya</div>
          </div>
          <div style={{ background:C.teal, borderRadius:8, padding:"4px 10px", fontSize:10, color:"#fff", fontWeight:600, cursor:"pointer" }}>+ New Booking</div>
        </div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr 1fr", gap:7, marginBottom:13 }}>
          {[{l:"Revenue",v:"₹14,200"},{l:"Appointments",v:"11"},{l:"Website",v:"108 visits"},{l:"Avg Ticket",v:"₹1,291"}].map((s,i) => (
            <div key={i} style={{ background:"rgba(255,255,255,0.07)", borderRadius:8, padding:"9px 8px" }}>
              <div style={{ color:"#fff", fontSize:13.5, fontWeight:800 }}>{s.v}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, marginTop:2 }}>{s.l}</div>
            </div>
          ))}
        </div>
        {!compact && (
          <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"11px 12px", marginBottom:11 }}>
            <div style={{ display:"flex", justifyContent:"space-between", marginBottom:8 }}>
              <span style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, fontWeight:600 }}>REVENUE THIS WEEK</span>
              <span style={{ color:C.tealBorder, fontSize:8.5, fontWeight:600 }}>₹68,400</span>
            </div>
            <div style={{ display:"flex", alignItems:"flex-end", gap:3, height:36 }}>
              {bars.map((h,i) => <div key={i} style={{ flex:1, borderRadius:"2px 2px 0 0", height:`${h}%`, background:i===6 ? C.teal : "rgba(0,101,101,0.4)" }} />)}
            </div>
          </div>
        )}
        <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"10px 12px" }}>
          <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, fontWeight:600, marginBottom:8 }}>TODAY&#39;S APPOINTMENTS</div>
          {appts.slice(0, compact ? 2 : 3).map((a, i) => (
            <div key={i} style={{ display:"flex", alignItems:"center", gap:7, padding:"5px 0", borderBottom:i < (compact ? 1 : 2) ? "1px solid rgba(255,255,255,0.06)" : "none" }}>
              <div style={{ width:24, height:24, borderRadius:6, background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9, fontWeight:800, color:"#fff", flexShrink:0 }}>
                {a.name.split(" ").map(n => n[0]).join("")}
              </div>
              <div style={{ flex:1, minWidth:0 }}>
                <div style={{ color:"#fff", fontSize:10.5, fontWeight:600, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{a.name} · {a.svc}</div>
                <div style={{ color:"rgba(255,255,255,0.3)", fontSize:8.5 }}>{a.time}</div>
              </div>
              <div style={{ background:a.status === "active" ? "rgba(255,200,0,0.15)" : a.status === "confirmed" ? "rgba(0,101,101,0.35)" : "rgba(255,255,255,0.08)", borderRadius:4, padding:"2px 7px", fontSize:8.5, color:a.status === "active" ? "#ffd93d" : a.status === "confirmed" ? C.tealBorder : "rgba(255,255,255,0.4)", flexShrink:0 }}>
                {a.status === "active" ? "Active" : a.status === "confirmed" ? "Confirmed" : "Upcoming"}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

