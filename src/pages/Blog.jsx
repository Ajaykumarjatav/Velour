import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function Blog({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeTag, setActiveTag] = useState("All");

  const tags = ["All","Operations","Marketing","Staff","Clients","Retail","Growth"];

  const posts = [
    {
      tag:"Operations",  featured:true,
      title:"How to reduce no-shows by 60% — without awkward reminder calls",
      excerpt:"No-shows are the silent revenue killer in every salon. A client who confirms on WhatsApp and does not show up costs you the slot, the revenue, and potentially the relationship. Here is exactly what changes when you use automated reminders the right way.",
      read:"6 min read", icon:"bell",
    },
    {
      tag:"Marketing",   featured:false,
      title:"The SMS campaign that filled a dead Tuesday — in 4 hours",
      excerpt:"Tuesday at 11am. Three open slots. A simple SMS to 80 clients: limited offer, book today, 15% off. Four hours later, two slots filled and one waitlisted. Here is the exact message, the segment used, and the result.",
      read:"4 min read", icon:"tag",
    },
    {
      tag:"Clients",     featured:false,
      title:"Why your regulars stop coming — and the one change that brings them back",
      excerpt:"The average beauty client drifts away silently. No complaint, no goodbye. They just stop booking. Understanding the 6-week cycle and building an automated follow-up is the single highest-ROI operational change most salons make.",
      read:"5 min read", icon:"repeat",
    },
    {
      tag:"Operations",  featured:false,
      title:"Walk-in management: how to serve every walk-in without disrupting booked appointments",
      excerpt:"Walk-ins are revenue opportunities — but without a system, they create front-desk chaos. A digital queue that integrates with your calendar changes everything. Here is how top salons handle 40% walk-in traffic without stress.",
      read:"4 min read", icon:"zap",
    },
    {
      tag:"Growth",      featured:false,
      title:"Why your salon needs its own booking website — not a marketplace listing",
      excerpt:"Fresha and Booksy list your salon alongside every competitor in your area. A client searching for a haircut sees your salon, your competitor, and their prices. Your own booking website changes this dynamic completely.",
      read:"5 min read", icon:"globe",
    },
    {
      tag:"Staff",       featured:false,
      title:"Staff scheduling for salons: how to build rosters that actually work",
      excerpt:"A roster built on a whiteboard or a WhatsApp group is a roster that breaks down weekly. Here is a practical guide to shift scheduling, leave management, and utilisation tracking for beauty teams of any size.",
      read:"6 min read", icon:"users",
    },
    {
      tag:"Retail",      featured:false,
      title:"Retail selling for salon owners: the low-pressure approach that actually works",
      excerpt:"Most salon staff hate selling retail. Most clients feel sold to. Here is a method that frames retail recommendations as genuine client care — and increases retail revenue without training anyone to be a salesperson.",
      read:"5 min read", icon:"bag",
    },
    {
      tag:"Operations",  featured:false,
      title:"The 3 numbers every salon owner should read every morning",
      excerpt:"Revenue, no-show rate, and booking conversion. These three numbers tell you everything about the health of your business on any given day. Here is exactly what to look for and how to act on what you see.",
      read:"4 min read", icon:"bar",
    },
    {
      tag:"Marketing",   featured:false,
      title:"Birthday campaigns: the automation that generates bookings on autopilot",
      excerpt:"A personalised birthday discount, sent automatically on the right day, has the highest conversion rate of any marketing message in the beauty industry. Here is how to set it up once and let it run forever.",
      read:"3 min read", icon:"gift",
    },
  ];

  const displayed = activeTag === "All" ? posts : posts.filter(p => p.tag === activeTag);
  const featured = posts.find(p => p.featured);
  const rest = displayed.filter(p => !p.featured || activeTag !== "All");

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:sm ? 32 : 48 }}>
            <Label center>For beauty and wellness business owners</Label>
            <H1 style={{ marginBottom:16, marginTop:10, maxWidth:600, margin:"10px auto 16px" }}>Practical guides. Operational insights. Real results.</H1>
            <Body muted center style={{ fontSize:sm ? 15 : 16.5, maxWidth:520, margin:"0 auto" }}>No generic business content. Everything written for the daily realities of running a salon, spa, or grooming studio.</Body>
          </div>
          {/* Featured post */}
          {featured && activeTag === "All" && (
            <div style={{ background:C.teal, borderRadius:R, padding:sm ? "28px 22px" : "44px 48px", display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 24 : 48, alignItems:"center", marginBottom:48, boxShadow:"0 20px 48px rgba(0,101,101,0.22)" }}>
              <div>
                <div style={{ display:"flex", gap:8, marginBottom:16 }}>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>Featured</Chip>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>{featured.tag}</Chip>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>{featured.read}</Chip>
                </div>
                <H2 light style={{ marginBottom:16, lineHeight:1.2 }}>{featured.title}</H2>
                <Body light style={{ lineHeight:1.75, marginBottom:24 }}>{featured.excerpt}</Body>
                <Btn v="whiteSolid" sm>Read Article</Btn>
              </div>
              <div style={{ background:"rgba(255,255,255,0.1)", borderRadius:R, padding:"32px 28px", border:"1px solid rgba(255,255,255,0.15)", display:"flex", flexDirection:"column", alignItems:"center", justifyContent:"center", textAlign:"center" }}>
                <div style={{ width:56, height:56, borderRadius:R, background:"rgba(255,255,255,0.15)", display:"flex", alignItems:"center", justifyContent:"center", marginBottom:16 }}>
                  <Ic n={featured.icon} sz={28} col="#fff" />
                </div>
                <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:"rgba(255,255,255,0.5)", letterSpacing:"0.08em", textTransform:"uppercase", marginBottom:8 }}>Most read this month</div>
                <div style={{ fontFamily:F, fontSize:16, fontWeight:700, color:"#fff" }}>No-shows drop by 60%</div>
                <div style={{ fontFamily:F, fontSize:13, color:"rgba(255,255,255,0.5)", marginTop:4 }}>after implementing automated reminders</div>
              </div>
            </div>
          )}
        </div>
      </section>

      {/* ── POSTS ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          {/* Tags */}
          <div style={{ display:"flex", gap:8, flexWrap:"wrap", marginBottom:32 }}>
            {tags.map((tag, i) => (
              <button key={i} onClick={() => setActiveTag(tag)} style={{ padding:"7px 18px", borderRadius:9999, border:`1.5px solid ${activeTag===tag ? C.teal : C.outlineVar}`, background:activeTag===tag ? C.teal : C.surface, color:activeTag===tag ? "#fff" : C.inkVar, fontWeight:600, fontSize:13, cursor:"pointer", fontFamily:F, transition:"all .18s" }}>
                {tag}
              </button>
            ))}
          </div>
          {/* Post grid */}
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:18 }}>
            {rest.map((post, i) => (
              <div key={i} style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, overflow:"hidden", cursor:"pointer", transition:"all .2s" }}
                onMouseEnter={e => { e.currentTarget.style.transform = "translateY(-4px)"; e.currentTarget.style.boxShadow = C.sh2; e.currentTarget.style.borderColor = C.teal; }}
                onMouseLeave={e => { e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; e.currentTarget.style.borderColor = C.outlineVar; }}>
                {/* Top bar */}
                <div style={{ background:C.tealLight, padding:"14px 18px", display:"flex", alignItems:"center", gap:10, borderBottom:`1px solid ${C.tealBorder}` }}>
                  <div style={{ width:34, height:34, borderRadius:8, background:C.surface, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                    <Ic n={post.icon} sz={16} col={C.teal} />
                  </div>
                  <div style={{ display:"flex", gap:6 }}>
                    <Chip teal sm>{post.tag}</Chip>
                    <span style={{ fontFamily:F, fontSize:11.5, color:C.inkVar, alignSelf:"center" }}>{post.read}</span>
                  </div>
                </div>
                {/* Content */}
                <div style={{ padding:"20px 20px 24px" }}>
                  <div style={{ fontFamily:F, fontSize:15.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{post.title}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.68 }}>{post.excerpt.substring(0, 120)}...</div>
                  <div style={{ marginTop:16, display:"flex", alignItems:"center", gap:6, color:C.teal }}>
                    <span style={{ fontFamily:F, fontSize:13, fontWeight:600, color:C.teal }}>Read article</span>
                    <Ic n="chevR" sz={13} col={C.teal} />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── NEWSLETTER ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px, maxWidth:640, margin:"0 auto", textAlign:"center" }}>
          <Label center>Stay in the loop</Label>
          <H2 center style={{ marginBottom:14, marginTop:10 }}>Practical salon insights in your inbox</H2>
          <Body muted center style={{ marginBottom:28 }}>Bi-weekly. Operational. No fluff. Unsubscribe any time.</Body>
          <div style={{ display:"flex", gap:10, flexDirection:sm ? "column" : "row" }}>
            <input placeholder="your@email.com" style={{ flex:1, height:48, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"0 16px", fontSize:15, fontFamily:F, outline:"none", boxSizing:"border-box" }}
              onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
              onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
            <Btn>Subscribe</Btn>
          </div>
          <Body sm muted style={{ marginTop:12, fontSize:12 }}>No spam. Bi-weekly insights for beauty business owners.</Body>
        </div>
      </section>
    </>
  );
}

