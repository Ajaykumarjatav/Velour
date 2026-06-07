import React, { useState, useEffect } from "react";
import { C, F, R } from "../../constants/theme";
import { brandLogoLight } from "../../constants/brand";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../icons/Icon";
import { Btn } from "../ui";

export function Nav({ nav }) {
  const { sm, md } = useW();
  const [mOpen, setMOpen] = useState(false);
  const [dd, setDd]       = useState(null);
  const [banner, setBanner] = useState(true);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const fn = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", fn);
    return () => window.removeEventListener("scroll", fn);
  }, []);

  const links = [
    { label:"Features",    items:["Appointments & Calendar","Staff Management","POS & Billing","Free Booking Website","Analytics & Reports","Marketing Campaigns","Client Management","Retail Inventory","Review Management","Multi-Location"] },
    { label:"By Business", items:["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"] },
    { label:"Pricing" },
    { label:"Resources",   items:["How It Works","Help Centre","Getting Started","Blog","About EasyGrox","Contact Us"] },
  ];

  const rm = { "Pricing":"pricing","Appointments & Calendar":"appointments","Staff Management":"staffMgmt","POS & Billing":"pos","Free Booking Website":"website","Analytics & Reports":"analytics","Marketing Campaigns":"marketing","Client Management":"clients","Retail Inventory":"retail","Review Management":"reviews","Multi-Location":"multiLocation","How It Works":"howItWorks","Help Centre":"helpCenter","Getting Started":"gettingStarted","Blog":"blog","About EasyGrox":"about","Contact Us":"contact" };
  const biz = ["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"];

  const go = (label, item) => {
    setDd(null); setMOpen(false);
    const t = item || label;
    if (biz.includes(t)) nav("bizType", { type: t });
    else if (rm[t]) nav(rm[t]);
    else if (label === "Pricing") nav("pricing");
    else if (label === "Features") nav("features");
    else nav("home");
  };

  return (
    <>
      {/* BANNER */}
      {banner && (
        <div style={{ background:C.teal, padding:"8px 16px", display:"flex", alignItems:"center", justifyContent:"center", gap:10, position:"relative", flexWrap:"wrap" }}>
          <span style={{ fontFamily:F, fontSize:13, color:"rgba(255,255,255,0.88)", textAlign:"center" }}>
            Every account includes a <strong style={{ color:"#fff" }}>free booking website</strong>
          </span>
          <button onClick={() => nav("website")} style={{ background:"rgba(255,255,255,0.18)", border:"1px solid rgba(255,255,255,0.35)", color:"#fff", padding:"3px 12px", borderRadius:9999, fontSize:11.5, cursor:"pointer", fontWeight:600, fontFamily:F, whiteSpace:"nowrap" }}>Learn more</button>
          <button onClick={() => setBanner(false)} style={{ position:"absolute", right:12, top:"50%", transform:"translateY(-50%)", background:"none", border:"none", color:"rgba(255,255,255,0.5)", cursor:"pointer", padding:4, display:"flex", alignItems:"center" }}>
            <Ic n="close" sz={14} col="rgba(255,255,255,0.5)" />
          </button>
        </div>
      )}

      {/* NAV BAR */}
      <nav style={{ background:scrolled ? "rgba(246,250,249,0.97)" : C.bg, backdropFilter:"blur(12px)", borderBottom:`1px solid ${C.outlineVar}`, height:60, display:"flex", alignItems:"center", position:"sticky", top:0, zIndex:200, boxShadow:scrolled ? C.sh1 : "none", transition:"all .2s" }}>
        <div style={{ maxWidth:1200, margin:"0 auto", padding:"0 16px", display:"flex", alignItems:"center", width:"100%", gap:0 }}>

          {/* LOGO */}
          <button onClick={() => go("home")} style={{ background:"none", border:"none", cursor:"pointer", display:"flex", alignItems:"center", marginRight:md ? 16 : 32, flexShrink:0, padding:0 }}>
            <img src={brandLogoLight} alt="EasyGrox" style={{ height:44, width:"auto", display:"block" }} />
          </button>

          {/* DESKTOP NAV */}
          {!md && (
            <div style={{ display:"flex", gap:0, flex:1, alignItems:"center" }}>
              {links.map((lnk, i) => (
                <div key={i} style={{ position:"relative" }} onMouseEnter={() => setDd(i)} onMouseLeave={() => setDd(null)}>
                  <button onClick={() => go(lnk.label)} style={{ background:"none", border:"none", cursor:"pointer", padding:"10px 12px", fontSize:13.5, fontWeight:500, color:dd === i ? C.teal : C.inkVar, fontFamily:F, display:"flex", alignItems:"center", gap:4, transition:"color .14s" }}>
                    {lnk.label}
                    {lnk.items && <Ic n="chevD" sz={10} col="inherit" />}
                  </button>
                  {lnk.items && dd === i && (
                    <div style={{ position:"absolute", top:"calc(100% + 4px)", left:0, background:C.surface, borderRadius:R, boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, padding:"6px 0", minWidth:220, zIndex:300 }}>
                      {lnk.items.map((item, ii) => (
                        <button key={ii} onClick={() => go(lnk.label, item)} style={{ display:"flex", alignItems:"center", gap:9, width:"100%", padding:"10px 16px", background:"none", border:"none", textAlign:"left", fontSize:13.5, color:C.inkVar, cursor:"pointer", fontFamily:F, transition:"all .1s" }}
                          onMouseEnter={e => { e.currentTarget.style.background = C.tealLight; e.currentTarget.style.color = C.teal; }}
                          onMouseLeave={e => { e.currentTarget.style.background = "none"; e.currentTarget.style.color = C.inkVar; }}>
                          <Ic n="chevR" sz={11} col={C.inkSoft} />{item}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}

          {/* DESKTOP BUTTONS */}
          {!md && (
            <div style={{ display:"flex", gap:8, alignItems:"center" }}>
              <Btn v="ghost" sm onClick={() => nav("login")}>Sign in</Btn>
              <Btn sm onClick={() => nav("signup")}>Start Free</Btn>
            </div>
          )}

          {/* MOBILE HAMBURGER */}
          {md && (
            <div style={{ display:"flex", gap:8, marginLeft:"auto", alignItems:"center" }}>
              <Btn sm onClick={() => nav("signup")}>Start Free</Btn>
              <button onClick={() => setMOpen(!mOpen)} style={{ background:"none", border:"none", cursor:"pointer", padding:6, display:"flex", alignItems:"center" }}>
                <Ic n={mOpen ? "close" : "menu"} sz={22} col={C.ink} />
              </button>
            </div>
          )}
        </div>
      </nav>

      {/* MOBILE MENU DRAWER */}
      {md && mOpen && (
        <div style={{ position:"fixed", top:0, left:0, right:0, bottom:0, zIndex:300, display:"flex", flexDirection:"column" }}>
          <div style={{ position:"absolute", inset:0, background:"rgba(24,28,28,0.5)", backdropFilter:"blur(4px)" }} onClick={() => setMOpen(false)} />
          <div style={{ position:"absolute", top:0, right:0, bottom:0, width:Math.min(320, window.innerWidth), background:C.surface, boxShadow:C.sh2, display:"flex", flexDirection:"column", overflowY:"auto" }}>
            <div style={{ padding:"16px 20px", borderBottom:`1px solid ${C.outlineVar}`, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
              <img src={brandLogoLight} alt="EasyGrox" style={{ height:36, width:"auto", display:"block" }} />
              <button onClick={() => setMOpen(false)} style={{ background:"none", border:"none", cursor:"pointer", padding:4 }}>
                <Ic n="close" sz={20} col={C.inkVar} />
              </button>
            </div>
            <div style={{ flex:1, padding:"12px 0" }}>
              {links.map((lnk, i) => (
                <div key={i}>
                  <button onClick={() => { if (!lnk.items) go(lnk.label); else setDd(dd === i ? null : i); }}
                    style={{ width:"100%", padding:"13px 20px", background:"none", border:"none", textAlign:"left", fontSize:15, fontWeight:600, color:C.ink, cursor:"pointer", fontFamily:F, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
                    {lnk.label}
                    {lnk.items && <Ic n={dd === i ? "chevD" : "chevR"} sz={14} col={C.inkVar} />}
                  </button>
                  {lnk.items && dd === i && (
                    <div style={{ background:C.low, borderTop:`1px solid ${C.outlineVar}`, borderBottom:`1px solid ${C.outlineVar}` }}>
                      {lnk.items.map((item, ii) => (
                        <button key={ii} onClick={() => go(lnk.label, item)} style={{ display:"block", width:"100%", padding:"11px 28px", background:"none", border:"none", textAlign:"left", fontSize:14, color:C.teal, cursor:"pointer", fontFamily:F, fontWeight:500 }}>
                          {item}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
            <div style={{ padding:"16px 20px", borderTop:`1px solid ${C.outlineVar}`, display:"flex", flexDirection:"column", gap:10 }}>
              <Btn full onClick={() => { setMOpen(false); nav("signup"); }}>Start for Free</Btn>
              <Btn v="outline" full onClick={() => { setMOpen(false); nav("login"); }}>Sign in</Btn>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
