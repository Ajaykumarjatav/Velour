import React from "react";
import { C, F, R } from "../../constants/theme";
import { Ic } from "../icons/Icon";

export function BookingMock({ small }) {
  const w = small ? 150 : 200;
  return (
    <div style={{ width:w, background:C.surface, borderRadius:R, overflow:"hidden", boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, flexShrink:0 }}>
      <div style={{ background:C.teal, height:20, display:"flex", alignItems:"center", justifyContent:"center" }}>
        <div style={{ width:40, height:4, background:"rgba(255,255,255,0.2)", borderRadius:3 }} />
      </div>
      <div style={{ padding:small ? 9 : 12 }}>
        <div style={{ textAlign:"center", marginBottom:9, paddingBottom:9, borderBottom:`1px solid ${C.outlineVar}` }}>
          <div style={{ width:26, height:26, borderRadius:"50%", background:C.tealLight, border:`1px solid ${C.tealBorder}`, margin:"0 auto 5px", display:"flex", alignItems:"center", justifyContent:"center" }}>
            <Ic n="scissors" sz={12} col={C.teal} />
          </div>
          <div style={{ fontSize:small ? 9.5 : 11, fontWeight:800, color:C.ink }}>Luxe Hair Studio</div>
          <div style={{ fontSize:8.5, color:C.inkVar }}>Jaipur · Book Online</div>
        </div>
        <div style={{ background:C.teal, borderRadius:R, padding:"6px", textAlign:"center", color:"#fff", fontSize:10, fontWeight:600, marginBottom:9, cursor:"pointer" }}>
          Book Appointment
        </div>
        {[{n:"Haircut",p:"₹500"},{n:"Colour",p:"₹1,200"},{n:"Keratin",p:"₹2,800"}].map((s,i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"4px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
            <span style={{ fontSize:9.5, color:C.ink, fontWeight:500 }}>{s.n}</span>
            <span style={{ fontSize:9.5, color:C.teal, fontWeight:700 }}>{s.p}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
