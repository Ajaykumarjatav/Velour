import React from "react";
import { I } from "../icons/iconSet";
import { C, F } from "../../constants/theme";
import { brandLogoDark } from "../../constants/brand";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../icons/Icon";
import { Chip } from "../ui";

export function Footer({ nav }) {
  const { sm } = useW();
  const cols = [
    { h:"Features",    links:["Appointments & Calendar","POS & Billing","Client Management","Staff Management","Retail Inventory","Free Booking Website","Analytics & Reports","Marketing Campaigns","Review Management","Multi-Location"] },
    { h:"By Business", links:["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"] },
    { h:"Company",     links:["How It Works","About EasyGrox","Blog","Contact Us","Help Centre","Getting Started","Pricing"] },
  ];
  const rm = { "Pricing":"pricing","Free Booking Website":"website","Analytics & Reports":"analytics","How It Works":"howItWorks","Help Centre":"helpCenter","Getting Started":"gettingStarted","Blog":"blog","About EasyGrox":"about","Contact Us":"contact","Appointments & Calendar":"appointments","Staff Management":"staffMgmt","POS & Billing":"pos","Marketing Campaigns":"marketing","Review Management":"reviews","Multi-Location":"multiLocation","Client Management":"clients","Retail Inventory":"retail" };
  const biz = ["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"];

  return (
    <footer style={{ background:C.ink, padding:"52px 0 0" }}>
      <div style={{ maxWidth:1200, margin:"0 auto", padding:"0 20px" }}>
        <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1.4fr 1fr 1fr 1fr", gap:sm ? 32 : 40, paddingBottom:44 }}>
          <div>
            <div style={{ marginBottom:14 }}>
              <img src={brandLogoDark} alt="EasyGrox" style={{ height:30, width:"auto", display:"block" }} />
            </div>
            <p style={{ fontFamily:F, fontSize:13.5, color:"rgba(255,255,255,0.42)", lineHeight:1.7, marginBottom:20 }}>Run your calendar, clients, and team in one calm place. Free booking website included.</p>
            <div style={{ display:"flex", gap:8 }}>
              {[I.mail, I.phone, I.link, I.globe].map((ic, i) => (
                <div key={i} style={{ width:34, height:34, borderRadius:8, background:"rgba(255,255,255,0.07)", border:"1px solid rgba(255,255,255,0.1)", display:"flex", alignItems:"center", justifyContent:"center", cursor:"pointer", color:"rgba(255,255,255,0.45)" }}
                  onMouseEnter={e => e.currentTarget.style.background = "rgba(255,255,255,0.14)"}
                  onMouseLeave={e => e.currentTarget.style.background = "rgba(255,255,255,0.07)"}>
                  {ic}
                </div>
              ))}
            </div>
          </div>
          {cols.map((col, ci) => (
            <div key={ci}>
              <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", color:"rgba(255,255,255,0.28)", textTransform:"uppercase", marginBottom:14 }}>{col.h}</div>
              {col.links.map((l, li) => (
                <button key={li} onClick={() => { if (biz.includes(l)) nav("bizType", { type:l }); else nav(rm[l] || "home"); }}
                  style={{ display:"block", background:"none", border:"none", color:"rgba(255,255,255,0.42)", fontSize:13.5, padding:"5px 0", cursor:"pointer", textAlign:"left", fontFamily:F, transition:"color .14s" }}
                  onMouseEnter={e => e.currentTarget.style.color = "#fff"}
                  onMouseLeave={e => e.currentTarget.style.color = "rgba(255,255,255,0.42)"}>
                  {l}
                </button>
              ))}
            </div>
          ))}
        </div>
        <div style={{ borderTop:"1px solid rgba(255,255,255,0.08)", padding:"18px 0", display:"flex", flexWrap:"wrap", justifyContent:"space-between", alignItems:"center", gap:10 }}>
          <span style={{ fontFamily:F, fontSize:12, color:"rgba(255,255,255,0.22)" }}>© 2026 EasyGrox · Privacy · Terms · Security</span>
          <Chip teal sm>Free website on every plan</Chip>
        </div>
      </div>
    </footer>
  );
}
