import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../../components/ui";
import { FeaturePageShell } from "../../components/layout/FeaturePageShell";
import {
  StatBadge, WorkflowStep, PainGain, Scenario, Quote,
  CalendarMock, POSMock, AnalyticsMock, ClientMock, StaffMock, WebsiteMock,
} from "../../components/shared/marketingBlocks";

export function AppointmentsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeTab, setActiveTab] = useState(0);

  const viewTypes = [
    { label:"Daily View", desc:"See every appointment across all staff columns for the current day. Perfect for the front desk — instant clarity on who is with which client, what is upcoming, and where gaps exist.", tags:["Online bookings","Walk-ins","Manual entries","Status colour codes"] },
    { label:"Weekly View", desc:"Plan the whole week at a glance. Spot over-booked days and under-utilised gaps. Drag appointments between days or staff members without losing any booking data.", tags:["Drag-and-drop reschedule","Staff workload balance","Gap identification","Week comparison"] },
    { label:"Monthly View", desc:"See the big picture — peak periods, slow weeks, holiday gaps. Use monthly view every Monday morning to pre-fill slow days before they happen.", tags:["Peak period planning","Holiday management","Promo timing","Capacity planning"] },
  ];

  return (
    <FeaturePageShell nav={nav} label="Appointments & Calendar" title="Your entire salon. One calendar. Total control."
      tagline="Every booking your salon handles — online, walk-in, phone, manual — lands on one live calendar. Staff assigned, time blocked, reminders sent. From the moment a client books to the moment they leave, EasyGrox handles the operational layer so your team just focuses on the work."
      mockup={<CalendarMock />}
      relatedPages={[{key:"pos",label:"POS & Billing"},{key:"clients",label:"Client Management"},{key:"staffMgmt",label:"Staff Management"},{key:"website",label:"Free Booking Website"}]}>

      {/* ─── 2. PAIN REALITY SECTION ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>The daily reality without EasyGrox</Label>
            <H2 center style={{ maxWidth:580, margin:"12px auto 0" }}>Does your morning look like this?</H2>
          </div>
          <PainGain
            pains={[
              "WhatsApp fills up overnight with booking requests you have to manually sort",
              "Two clients arrive at 11am for the same stylist — double-booking chaos",
              "Walk-ins interrupt scheduled clients and you have no system to manage the queue",
              "3 no-shows today because reminders were sent manually (or not at all)",
              "Your team has no idea who they are seeing next — checking a paper register",
              "You cannot see which hours are peak and which are dead without a spreadsheet",
            ]}
            gains={[
              "Online bookings fill your calendar automatically while you sleep",
              "Double-bookings are impossible — the system blocks conflicting time slots",
              "Walk-ins added in 10 seconds and assigned to the next available staff",
              "Every client gets automatic SMS/WhatsApp 24h before — no-shows drop by 60%",
              "Every staff member sees their own day on their phone — no paper needed",
              "Peak hour heatmap shows you exactly when demand is highest",
            ]}
          />
        </div>
      </section>

      {/* ─── 3. WORKFLOW WALKTHROUGH ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>A booking from start to finish</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>How a booking actually flows through EasyGrox</H2>
              <Body muted style={{ marginBottom:32 }}>From the moment a client books online to the moment they walk out — every step is handled without your team lifting a finger.</Body>
              <WorkflowStep n="1" title="Client books online, any time" desc="Client visits your free EasyGrox booking website, browses services and stylist availability, and confirms — 2am or 2pm, it works." note="From your free booking website" />
              <WorkflowStep n="2" title="Appointment lands in calendar instantly" desc="The booking appears in your calendar, assigned to the correct staff member, with the service duration blocked." note="Zero manual entry" />
              <WorkflowStep n="3" title="Confirmation SMS sent automatically" desc="Client receives a branded confirmation message with appointment time, location, and a one-tap reschedule link." note="Automatic" />
              <WorkflowStep n="4" title="Reminder sent 24 hours before" desc="Client gets a WhatsApp or SMS reminder the day before — reducing no-shows to an industry-low 4-6%." note="Configurable timing" />
              <WorkflowStep n="5" title="Staff notified on their personal dashboard" desc="Each stylist sees their own upcoming appointments, can check client history and colour formulas before arrival." note="Staff mobile view" />
              <WorkflowStep n="6" title="Client arrives, appointment marked active" desc="Front desk taps the appointment to mark it in-progress. Status updates live across all staff dashboards." note="Real-time updates" />
              <WorkflowStep n="7" title="Service complete — bill opened instantly" desc="Tap Complete and the billing screen opens pre-loaded with services. Bill in under 60 seconds." note="Connected to POS" final />
            </div>
            <div style={{ position:"sticky", top:80 }}>
              <CalendarMock />
              <div style={{ marginTop:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:10 }}>
                <StatBadge value="60%" label="Fewer no-shows" good />
                <StatBadge value="10s" label="Walk-in added" good />
                <StatBadge value="0" label="Double-bookings possible" good />
                <StatBadge value="24/7" label="Online booking open" good />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 4. CALENDAR VIEWS ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Three views. One calendar.</Label>
            <H2 center>See your business the way you need to</H2>
          </div>
          <div style={{ display:"flex", gap:8, justifyContent:"center", marginBottom:32, flexWrap:"wrap" }}>
            {viewTypes.map((v, i) => (
              <button key={i} onClick={() => setActiveTab(i)} style={{ padding:"9px 20px", borderRadius:9999, border:`1.5px solid ${activeTab === i ? C.teal : C.outlineVar}`, background:activeTab === i ? C.teal : C.surface, color:activeTab === i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 13 : 13.5, cursor:"pointer", transition:"all .18s", fontFamily:F }}>
                {v.label}
              </button>
            ))}
          </div>
          <Card style={{ padding:"32px 28px" }}>
            <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:40, alignItems:"center" }}>
              <div>
                <H3 style={{ marginBottom:14 }}>{viewTypes[activeTab].label}</H3>
                <Body muted style={{ marginBottom:22, fontSize:15 }}>{viewTypes[activeTab].desc}</Body>
                <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                  {viewTypes[activeTab].tags.map((t, i) => <Chip key={i} teal sm>{t}</Chip>)}
                </div>
              </div>
              <CalendarMock />
            </div>
          </Card>
        </div>
      </section>

      {/* ─── 5. REAL SCENARIOS ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Real situations. Real solutions.</Label>
            <H2 center>How EasyGrox handles the daily chaos</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="🚶" title="Walk-in during a full afternoon" desc="Your 3pm rush — 3 stylists all booked. A walk-in arrives asking for a quick trim." outcome="Front desk checks the calendar in real time, spots Raj has a 20-min buffer between 3:40 and 4pm, slots the walk-in instantly. No chaos." />
            <Scenario emoji="❌" title="Client cancels 2 hours before" desc="Meena cancels her 2pm keratin treatment with 2 hours notice. That is 2 hours of Priya's time unbooked." outcome="EasyGrox flags the gap. You run a quick WhatsApp blast to the waiting-list segment. The slot fills in 40 minutes." />
            <Scenario emoji="📱" title="Late-night online booking surge" desc="You post a reel at 9pm. 12 clients try to book between 9pm and midnight." outcome="All 12 land directly in your calendar — assigned to the correct stylist, time blocked, confirmations sent. You wake up to a full tomorrow." />
            <Scenario emoji="💇" title="Regular client colour appointment" desc="Ananya books her regular monthly colour with Priya. It is a 3-hour service." outcome="EasyGrox blocks 3 hours of Priya's calendar, stores the colour formula in the appointment card, and sends a reminder with prep instructions the day before." />
            <Scenario emoji="🎂" title="Birthday booking peak — December" desc="December is your busiest month. You need to see exactly which days are overloaded and which have gaps." outcome="Monthly view shows the full December grid. You spot two over-booked Saturdays and open online slots for Sundays to absorb the overflow." />
            <Scenario emoji="⏰" title="Staff member calls in sick" desc="Sunita calls in sick at 7am. She has 6 appointments today." outcome="Open Sunita's day view, bulk-reassign her appointments to available staff with one action. All 6 clients get automatic SMS notifications." />
          </div>
        </div>
      </section>

      {/* ─── 6. CAPABILITIES GRID ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Full capability set</Label>
            <H2 center>Every appointment management tool you need</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"calendar", t:"Live Calendar",         d:"Real-time calendar that updates instantly across all devices as bookings arrive." },
              { n:"users",    t:"Staff Column View",     d:"See every team member side by side on the same screen." },
              { n:"globe",    t:"Online Booking",        d:"Clients book from your free website 24/7. Appears in calendar instantly." },
              { n:"zap",      t:"Walk-In in 10 Seconds", d:"Tap time slot, select service, assign staff — confirmed." },
              { n:"bell",     t:"Auto Reminders",        d:"SMS and WhatsApp before every appointment. Configurable timing." },
              { n:"refresh",  t:"Drag-and-Drop Move",   d:"Reschedule by dragging. Client notified automatically." },
              { n:"clock",    t:"Buffer Time",           d:"Set automatic gaps between appointments per service or per staff." },
              { n:"shield",   t:"Double-Booking Guard",  d:"System blocks conflicting bookings. Impossible to double-book." },
              { n:"repeat",   t:"Recurring Appointments",d:"Set weekly or monthly recurring bookings for regular clients." },
              { n:"tag",      t:"Booking Labels",        d:"Colour-code by service type, status, or staff member." },
              { n:"bar",      t:"Peak Hour Heatmap",     d:"See which hours drive the most bookings. Schedule accordingly." },
              { n:"lock",     t:"Role-Based Calendar",   d:"Front desk sees all. Staff see only their own appointments." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ─── 7. SOCIAL PROOF ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>What owners say</Label>
            <H2 center>From salons already running on EasyGrox</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            <Quote metric="No-shows dropped from 22% to 4%" text="The automatic reminders changed everything. Before EasyGrox I was manually messaging clients the night before. Now it just happens and my no-show rate has crashed." name="Priya Sharma" role="Luxe Hair Studio, Jaipur" />
            <Quote metric="8 extra bookings per week" text="Online booking through my EasyGrox website fills my calendar overnight. I wake up to 3-4 new appointments that came in after 9pm. That used to be zero." name="Ravi Nair" role="The Barbers Co., Kochi" />
            <Quote metric="Staff spend 0 mins on scheduling" text="My team used to spend 20 minutes every morning cross-referencing paper registers. Now they open the app and their day is there. Walk-ins no longer cause panic." name="Sunita Patel" role="Studio One, Ahmedabad" />
          </div>
        </div>
      </section>

      {/* ─── 8. SETUP / ACTIVATION ─── */}
      <section style={{ padding:SP, background:C.tealLight, borderTop:`1px solid ${C.tealBorder}` }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:48, alignItems:"center" }}>
            <div>
              <Label>Business activation</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your calendar is live in under 30 minutes</H2>
              <Body muted style={{ marginBottom:28, fontSize:16 }}>After signup, the app guides you through a step-by-step setup checklist. When you add your services and staff, your booking calendar activates automatically.</Body>
              <div style={{ display:"flex", flexDirection:"column", gap:12 }}>
                {[
                  { step:"Create account here (30 seconds)", done:true },
                  { step:"Add your services and duration (10 minutes)", done:false },
                  { step:"Add staff and assign services (5 minutes)", done:false },
                  { step:"Set your opening hours (2 minutes)", done:false },
                  { step:"Publish your booking website (1 click)", done:false },
                ].map((s, i) => (
                  <div key={i} style={{ display:"flex", gap:10, alignItems:"center" }}>
                    <div style={{ width:22, height:22, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`2px solid ${s.done ? C.teal : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      {s.done && <Ic n="check" sz={12} col="#fff" />}
                    </div>
                    <span style={{ fontFamily:F, fontSize:14, color:s.done ? C.teal : C.inkVar, fontWeight:s.done ? 600 : 400 }}>{s.step}</span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, padding:"28px 24px", border:`1px solid ${C.tealBorder}`, boxShadow:C.sh1 }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:20 }}>
                  <H4>Setup Progress</H4>
                  <span style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.teal }}>40% complete</span>
                </div>
                <div style={{ background:C.tealLight, borderRadius:9999, height:8, marginBottom:24, overflow:"hidden" }}>
                  <div style={{ width:"40%", height:"100%", background:C.teal, borderRadius:9999 }} />
                </div>
                {["Business details","Services added","Staff added","Hours set","Website published"].map((item, i) => (
                  <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"10px 0", borderBottom:i < 4 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center" }}>
                      <div style={{ width:20, height:20, borderRadius:"50%", background:i < 2 ? C.teal : C.surface, border:`1.5px solid ${i < 2 ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        {(i < 2) && <Ic n="check" sz={11} col="#fff" />}
                      </div>
                      <span style={{ fontFamily:F, fontSize:13.5, color:i < 2 ? C.teal : C.inkVar, fontWeight:i < 2 ? 600 : 400 }}>{item}</span>
                    </div>
                    {i < 2 ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:12, color:C.inkSoft }}>Pending</span>}
                  </div>
                ))}
                <Btn full onClick={() => nav("signup")} style={{ marginTop:20 }}>Complete Setup Now</Btn>
              </div>
            </div>
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}
