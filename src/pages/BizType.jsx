import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";
import { DashMock } from "../components/mockups/DashMock";
import {
  CalendarMock, StaffMock, ClientMock, AnalyticsMock, WebsiteMock,
  PainGain, WorkflowStep,
} from "../components/shared/marketingBlocks";

export function BizType({ nav, type }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "56px 0" : "88px 0";
  const [openFaq, setOpenFaq] = useState(null);
  const [activeSvc, setActiveSvc] = useState(0);

  const iconMap = {
    "Hair Salon":"scissors","Barber Shop":"razor","Nail Studio":"nail",
    "Spa & Massage":"lotus","Makeup Artist":"lipstick","Tattoo Studio":"ink","Pet Grooming":"paw"
  };
  const icon = iconMap[type] || "scissors";

  const BD = {
    "Hair Salon": {
      sub:"Stylists · Colour Treatments · Walk-ins · Retail",
      heroHead:"Run your hair salon like a well-oiled machine — from the first booking to the final invoice.",
      heroSub:"Manage every stylist's calendar, track colour formulas, bill services and retail in one place, and give clients a free booking website to book from anywhere.",
      stats:[{v:"60%",l:"Fewer no-shows"},{v:"30 min",l:"Setup time"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"phone",  t:"Booking chaos on WhatsApp",          d:"Appointment requests come in at all hours — DMs, calls, messages — and you spend your evenings sorting through them instead of resting."},
        {icon:"calendar",t:"Double-bookings and overlaps",       d:"Two clients land at the same time for the same stylist because there is no shared calendar and no blocking system."},
        {icon:"user",   t:"Colour formulas lost forever",        d:"Each stylist keeps their own notes — or nothing at all. When a client returns and their stylist is off, no one knows what formula was used."},
        {icon:"bag",    t:"Retail disappears without a trace",   d:"Shampoos and serums are sold informally at the desk — no record, no stock tracking, no idea which products generate the most revenue."},
        {icon:"repeat", t:"Clients stop returning silently",     d:"A regular who used to come every 5 weeks has not been in for 9 weeks. You have no system to flag this or reach out before they choose someone else."},
        {icon:"bar",    t:"End-of-day revenue is a guessing game",d:"Counting receipts, estimating tips, reconciling cash — the daily financial close takes 30 minutes and is still inaccurate."},
      ],
      workflow:[
        {n:"1",icon:"globe",  t:"Client books online — any time",  d:"Your EasyGrox booking website is live 24/7. Clients browse services, choose their preferred stylist, and pick a slot — at midnight or 6am. The booking lands in your calendar instantly."},
        {n:"2",icon:"calendar",t:"Calendar blocks the slot",       d:"The stylist's calendar is updated immediately. No double-booking is possible. Walk-ins are added at the front desk in 10 seconds with the same system."},
        {n:"3",icon:"bell",   t:"Reminders go out automatically", d:"The client gets a confirmation SMS immediately after booking. 24 hours before their appointment, another reminder fires. No manual messaging needed."},
        {n:"4",icon:"scissors",t:"Stylist reviews client history", d:"Before the appointment, the stylist checks the client profile — previous colour formula, service history, notes, and preferences. Every visit, every product, right there."},
        {n:"5",icon:"pos",    t:"Billing done in 60 seconds",      d:"Tap Complete on the appointment. The POS opens pre-loaded with the service. Add any retail sold, apply a discount, collect payment. Digital receipt sent via WhatsApp."},
        {n:"6",icon:"tag",    t:"Automated follow-up triggered",   d:"3 days later, the client gets a follow-up message. 5 weeks out, a rebooking nudge. Loyalty milestones trigger automatic birthday offers and VIP rewards."},
      ],
      features:[
        {n:"calendar",t:"Stylist-Specific Calendar",d:"Every stylist has their own column. Online bookings, walk-ins, and blocks all in one view."},
        {n:"user",    t:"Colour Formula Storage",   d:"Formula, developer strength, processing time, toner — saved permanently to every client profile."},
        {n:"pos",     t:"Service + Retail Billing", d:"Services and shampoos in one transaction. All payment types. Receipt via WhatsApp."},
        {n:"bag",     t:"Retail Inventory",         d:"Track every bottle on your shelf. Low-stock alerts before you run out."},
        {n:"bell",    t:"Auto Reminders",           d:"Booking confirmation + 24h reminder. Salons report up to 60% fewer no-shows."},
        {n:"tag",     t:"Marketing Campaigns",      d:"SMS birthday offers, re-engagement for lapsed clients, flash promotions for slow days."},
        {n:"bar",     t:"Revenue Analytics",        d:"Revenue by stylist, by service category, by day. Peak hours heatmap. Compare weeks."},
        {n:"globe",   t:"Free Booking Website",     d:"Mobile-ready, SEO-indexed, live 24/7. Auto-generated from your store setup."},
      ],
      staffPoints:["Each stylist logs in to their own dashboard — their schedule, their clients, their performance","Assign services to specific stylists — clients only see that stylist when booking that service","Track revenue generated, appointments completed, and client rating per stylist this month","Shift scheduling and leave approval managed inside the app — no WhatsApp back-and-forth","Set role-based access so junior staff see only their work, not financial data"],
      clientPoints:["Every client has a permanent profile with colour formulas, allergies, and visit history","Clients who return to a different stylist are served from the same record — no awkward questions","Automatic rebooking nudge at the right interval — your regulars never slip away silently","VIP status auto-assigned at spend threshold — use it for priority booking or exclusive offers","Birthday and first-visit anniversary offers sent automatically — zero manual effort"],
      metrics:[{v:"₹1,24,800",l:"Avg monthly revenue tracked"},{v:"74%",l:"Client retention rate"},{v:"4.1%",l:"No-show rate (industry avg: 8%)"},{v:"48%",l:"Bookings from website"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your services with prices and durations", where:"App setup · 10 min", done:false},
        {step:"Create stylist profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set your salon hours and booking rules", where:"App setup · 3 min", done:false},
        {step:"Preview and publish your free booking website", where:"One click · instant", done:false},
        {step:"Add your booking link to Instagram and WhatsApp", where:"Share · 2 min", done:false},
      ],
      testimonial:{q:"Before EasyGrox I lost hours every week managing WhatsApp bookings. Now my clients book online, my team has their own schedules, and I see each stylist revenue every morning. Setup took 25 minutes.", n:"Priya Sharma", r:"Luxe Hair Studio, Jaipur", metric:"8 more bookings per week"},
      faqs:[
        {q:"Can each stylist see only their own appointments?",a:"Yes. Each stylist gets their own login with a personal dashboard showing only their schedule, their tasks, and their client notes. They cannot see financial data or other staff records unless you give them access."},
        {q:"How does colour formula storage work?",a:"When a stylist completes a colour service, they add the formula to the client profile — developer strength, timing, toner used, and notes. It is saved permanently and visible to any stylist who serves that client in the future."},
        {q:"Does the booking website show individual stylists?",a:"Yes. Clients can browse your service menu and choose a preferred stylist. The website shows each stylist's availability in real time based on their EasyGrox calendar."},
        {q:"How does online booking connect to my calendar?",a:"Instantly and automatically. When a client books through your EasyGrox website, the appointment appears in your calendar in real time — no manual transfer, no phone call, no notification to action."},
      ],
    },
    "Barber Shop": {
      sub:"Queues · Fast Cuts · Walk-ins · Barber Performance",
      heroHead:"Keep your chairs full, your queues moving, and your barbers performing — all from one dashboard.",
      heroSub:"Walk-in queue management, fast billing for quick services, barber-specific schedules, and a free booking website so clients can book ahead and skip the wait.",
      stats:[{v:"10s",l:"Walk-in added"},{v:"60s",l:"Bill any client"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"users",   t:"Walk-ins pile up with no system",     d:"Three people walk in at once. Your barbers are booked. No one knows who is next, who has been waiting longest, or how long the wait will be."},
        {icon:"phone",   t:"Calls during your busiest hour",      d:"Saturday at noon, your phone rings constantly — clients asking if there is a wait. You lose focus mid-cut every time."},
        {icon:"pos",     t:"Daily revenue counted manually",      d:"End of day you count cash, try to recall how many card payments were made, and estimate tips. The total never quite adds up."},
        {icon:"bar",     t:"No barber performance comparison",    d:"You have 3 barbers but no idea which one generates the most revenue, serves the most clients, or gets the best feedback."},
        {icon:"bag",     t:"Retail sold but never tracked",       d:"Wax, beard oil, and styling products are sold from the counter but no record exists — no stock tracking, no sales history, no reorder system."},
        {icon:"repeat",  t:"Regulars drift away quietly",         d:"A regular who came every 2 weeks has not been in for 6 weeks. You have no alert, no system, and no way to reach out before he finds another shop."},
      ],
      workflow:[
        {n:"1",icon:"zap",    t:"Walk-in arrives — logged in 10 seconds", d:"Front desk opens EasyGrox, taps Walk-In, selects the service and the next available barber. Logged, assigned, and in the queue. No paper, no confusion."},
        {n:"2",icon:"calendar",t:"Advance booking fills gaps automatically",d:"Clients who booked through your website pre-fill your morning calendar. You walk in knowing exactly which barbers are booked and which slots are open."},
        {n:"3",icon:"bell",   t:"Reminder keeps clients from forgetting",  d:"Every advance booking gets an automatic SMS the morning of their appointment. Clients arrive on time. Barbers are not left waiting for a no-show."},
        {n:"4",icon:"pos",    t:"Fast billing — three taps",               d:"Service complete. Tap Complete. Select payment method. Collect. Receipt sent. Under 60 seconds. Queue moves. Clients do not wait at the counter."},
        {n:"5",icon:"bar",    t:"Daily summary ready at close",            d:"Total revenue, number of clients, payment method split, and each barber's individual tally — available the moment you close for the day."},
        {n:"6",icon:"repeat", t:"Regular client nudge fires automatically",d:"3 weeks since his last visit? The system sends him a personalised message — no action from you, no manual tracking required."},
      ],
      features:[
        {n:"zap",     t:"Walk-In Queue",          d:"Add a walk-in in 10 seconds. Assign to the next available barber. Queue managed digitally."},
        {n:"calendar",t:"Barber Schedules",        d:"Each barber has their own bookable calendar. Clients see real-time availability."},
        {n:"pos",     t:"Fast Checkout",           d:"Three taps from service complete to payment collected. Never a queue at the counter."},
        {n:"bag",     t:"Retail Tracking",         d:"Track wax, gel, and beard products. Low-stock alert when a product runs low."},
        {n:"bar",     t:"Barber Performance",      d:"Revenue, appointments, and client rating per barber. Compare month on month."},
        {n:"bell",    t:"Automated Reminders",     d:"Morning reminder for every advance booking. No-shows drop significantly."},
        {n:"repeat",  t:"Lapsed Client Campaigns", d:"Automatically message regulars who have not been in for 3+ weeks."},
        {n:"globe",   t:"Free Booking Website",    d:"Clients book ahead and skip the walk-in wait. Your chairs stay full."},
      ],
      staffPoints:["Each barber has their own login — they see their schedule, their earnings, their clients","Set which services each barber performs — clients only see available barbers for each service","Track cuts per day, revenue per month, and average client rating per barber","Leave requests submitted through the app — automatically blocked in the calendar when approved","Front desk role sees bookings and billing — not the owner-level financial dashboard"],
      clientPoints:["Every returning client has a profile — preferences, past services, product purchases","Clients who book ahead skip the walk-in queue — your loyal regulars feel rewarded","Automatic 3-week nudge keeps regulars from drifting to another shop","Birthday offer sent automatically on the right day — personal touch, zero effort","Online booking available 24/7 — clients book from Instagram at 10pm, arrive next morning"],
      metrics:[{v:"312",l:"Appointments managed monthly"},{v:"3 wk",l:"Avg return frequency"},{v:"91%",l:"Chair utilisation rate"},{v:"4.8★",l:"Avg client rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your services — haircut, beard trim, etc.", where:"App setup · 8 min", done:false},
        {step:"Create barber profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set opening hours and walk-in preferences", where:"App setup · 3 min", done:false},
        {step:"Publish your free booking website", where:"One click · instant", done:false},
        {step:"Share booking link — Instagram bio, WhatsApp status", where:"2 min", done:false},
      ],
      testimonial:{q:"I added the EasyGrox booking link to my Instagram and within a week I had clients booking ahead for the first time. My Saturday rush is now half walk-ins, half advance — it runs so much smoother.", n:"Ravi Kumar", r:"Classic Cuts, Pune", metric:"Chair utilisation up to 91%"},
      faqs:[
        {q:"How does walk-in management work?",a:"When a walk-in arrives, your front desk taps Walk-In in EasyGrox, selects the service and assigns a barber. The appointment is logged instantly. All walk-ins and advance bookings appear on the same calendar so barbers always know what is next."},
        {q:"Can clients book a specific barber online?",a:"Yes. Your EasyGrox booking website shows each barber's available time slots. Clients can choose a barber or select Any available and the system assigns automatically based on availability."},
        {q:"Does EasyGrox work for a single-barber shop?",a:"Yes. The Solo plan is designed specifically for independent barbers or single-operator shops. One login, one calendar, full POS, and your free booking website — all included."},
        {q:"How does the lapsed client re-engagement work?",a:"You set the interval — 3 weeks, 4 weeks, whatever fits your service. When a regular client exceeds that interval, EasyGrox flags them and automatically sends a personalised WhatsApp or SMS message with an offer to rebook."},
      ],
    },
    "Nail Studio": {
      sub:"Nail Technicians · Gel & Art · Retail Products",
      heroHead:"Every nail technician booked, every client preference remembered, every retail product tracked.",
      heroSub:"Precision scheduling for nail studios — service-specific booking, technician columns, client nail art notes, retail inventory, and a free booking website that works while you work.",
      stats:[{v:"0",l:"Double-bookings possible"},{v:"24/7",l:"Online booking"},{v:"10s",l:"Walk-in logged"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"calendar",t:"Technician bookings overlap",          d:"Without a shared calendar, two technicians get double-booked for the same slot. One client waits, one leaves. The morning starts badly."},
        {icon:"phone",   t:"After-hours booking requests flood in", d:"Clients DM at 11pm asking for gel refill slots next week. You see 14 unread messages in the morning and spend 30 minutes sorting them out."},
        {icon:"bag",     t:"Gel and polish stock runs out mid-week",d:"A client wants a specific OPI shade and it ran out 3 days ago. No alert fired. No reorder was made. You have to explain and suggest an alternative."},
        {icon:"user",    t:"Client preferences are in someone head",d:"Which base coat works for Priya? What shape does she always get? What shade was it last time? If her regular technician is off, no one knows."},
        {icon:"clock",   t:"Refill timing is guesswork",           d:"Gel nails need a refill every 3 weeks. You have no system to know which clients are due — and no way to remind them before they book elsewhere."},
        {icon:"bar",     t:"Retail sales are invisible",            d:"Polish, cuticle oil, and nail tools are sold at the counter with no record. At month-end, you have no idea what your retail revenue was."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client books through your website",    d:"Your nail studio booking page shows each technician's services and available slots. Clients pick their service — gel refill, full set, nail art — and confirm. Booking appears instantly."},
        {n:"2",icon:"calendar",t:"Technician column updated in real time",d:"The right technician sees the new booking in their calendar. Duration blocked precisely — gel nails and nail art have different durations and the system knows each."},
        {n:"3",icon:"bell",    t:"Reminder sent automatically",           d:"Client gets a confirmation immediately and a reminder 24 hours before. The reminder includes prep instructions — remove old gel at home, arrive with clean nails."},
        {n:"4",icon:"user",   t:"Technician reviews client notes",       d:"Before the appointment, the technician checks the client card — preferred shape, favourite colour family, skin sensitivities, last nail art design. Every visit builds on the last."},
        {n:"5",icon:"pos",    t:"Billing includes service and retail",   d:"Gel full set plus the OPI polish the client wanted to take home — one transaction. Inventory auto-deducted. Receipt sent via WhatsApp."},
        {n:"6",icon:"repeat", t:"Refill reminder fires at 3 weeks",      d:"21 days after the appointment, EasyGrox sends the client an automatic refill reminder. Clients rebook with you — not with the studio down the street."},
      ],
      features:[
        {n:"calendar",t:"Technician Column View",  d:"Each technician has their own bookable column. Service durations set precisely per service."},
        {n:"user",    t:"Client Nail Notes",        d:"Shape preference, colour family, allergies, and past designs stored permanently."},
        {n:"repeat",  t:"Refill Reminders",         d:"Automated 3-week refill nudge. Clients rebook before they look elsewhere."},
        {n:"bag",     t:"Polish and Gel Inventory", d:"Track every product. Low-stock alert before your best-selling shade runs out."},
        {n:"pos",     t:"Retail + Service Billing", d:"Sell a polish take-home alongside the service — one receipt, inventory auto-deducted."},
        {n:"bell",    t:"Auto Reminders",           d:"Confirmation + 24h reminder with prep instructions included."},
        {n:"bar",     t:"Service Analytics",        d:"Which services are growing? Which technician earns the most? Weekly comparison."},
        {n:"globe",   t:"Free Booking Website",     d:"Showcase your nail art portfolio. Clients book a specific technician online."},
      ],
      staffPoints:["Each nail technician has their own calendar column — clients choose them specifically","Set service specialisations — only show gel technicians when a client books gel services","Track revenue generated, services completed, and client satisfaction per technician","Leave requests block their calendar automatically — no manual schedule editing","Junior technicians only see their own column — no access to studio financials"],
      clientPoints:["Client profile stores every nail visit — colour, shape, art design, and products used","Any technician can serve a client with full context — no repeated questions","3-week refill reminder keeps clients returning before they drift away","Preference notes mean every visit feels personalised — not generic","Portfolio shown on booking website builds confidence before first visit"],
      metrics:[{v:"3 wks",l:"Avg refill return cycle"},{v:"82%",l:"Client rebooking rate"},{v:"₹680",l:"Avg retail per client"},{v:"0",l:"Double-bookings"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add nail services — gel, full set, nail art, removal", where:"App setup · 10 min", done:false},
        {step:"Add technician profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set studio hours and booking lead time", where:"App setup · 3 min", done:false},
        {step:"Publish your free booking website with nail art portfolio", where:"One click", done:false},
        {step:"Add booking link to Instagram bio and WhatsApp", where:"2 min", done:false},
      ],
      testimonial:{q:"My clients used to DM me at midnight for gel refill slots. Now they book through my EasyGrox website and I wake up to a filled calendar. The 3-week refill reminder has been a game changer — my rebooking rate went from 60% to 82%.", n:"Meera Joshi", r:"Gloss Nail Studio, Mumbai", metric:"Rebooking rate 60% to 82%"},
      faqs:[
        {q:"Can I set different durations for different nail services?",a:"Yes. When you add each service, you set the duration individually. Gel full set takes 90 minutes, nail art takes 120, a basic manicure takes 45. The booking system blocks exactly the right amount of time for each."},
        {q:"How does the refill reminder work?",a:"When a gel nail appointment is completed, EasyGrox automatically schedules a reminder message to go to the client 21 days later. The message is personalised with their name and a direct booking link. You set it once and it runs for every gel appointment automatically."},
        {q:"Can clients book a specific technician online?",a:"Yes. Your booking website shows each technician's profile, specialisations, and available time slots. Clients choose the technician or select Any and the system assigns based on availability."},
        {q:"How is retail inventory tracked?",a:"Every retail product sold through the EasyGrox POS automatically deducts from your inventory count. When any product drops below your set minimum stock level, an alert appears on your dashboard so you can reorder before running out."},
      ],
    },
    "Spa & Massage": {
      sub:"Therapists · Treatment Rooms · Packages · Wellness Memberships",
      heroHead:"Fill your treatment rooms, retain your wellness clients, and manage your therapists — all in one calm platform.",
      heroSub:"Room scheduling, therapist assignment, wellness package management, multi-session tracking, and a free booking website — built for spas and massage centres that want to operate with clarity.",
      stats:[{v:"0",l:"Room double-bookings"},{v:"100%",l:"Package tracking"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"calendar",t:"Room double-bookings happen too often",    d:"Without a room management layer, two therapists book the same treatment room for overlapping sessions. One client arrives to find the room in use."},
        {icon:"invoice", t:"Packages and memberships tracked on paper",d:"A client bought a 5-session deep tissue package. How many sessions have they used? The notebook is not in front of you right now and neither is the answer."},
        {icon:"bar",     t:"Therapist utilisation completely invisible",d:"Some therapists are overbooked while others have idle hours. You discover this at month-end, not when you could do something about it."},
        {icon:"clock",   t:"Long sessions need precise buffer time",   d:"A 90-minute hot stone massage needs 15 minutes cleanup between sessions. Without buffer management, the next client arrives before the room is ready."},
        {icon:"repeat",  t:"Wellness clients need gentle follow-up",   d:"A client who comes monthly for a deep tissue session missed last month. No alert. No outreach. By the time you notice, they have found a closer spa."},
        {icon:"gift",    t:"Packages expire without either party knowing",d:"A client bought a 3-month membership 4 months ago. You have no system to flag expired packages or prompt a renewal before the relationship goes cold."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client books a session online",         d:"Your spa booking website shows every service with duration and pricing. Clients book a therapist, choose a session length, and confirm. Room is automatically assigned."},
        {n:"2",icon:"calendar",t:"Room blocked with buffer automatically", d:"The booking blocks the treatment room for the session duration plus your configured buffer time. The next booking cannot start until the room is cleared."},
        {n:"3",icon:"bell",    t:"Preparation reminder sent to therapist", d:"30 minutes before the session, the therapist's dashboard shows the upcoming client — their package history, preferences, pressure notes, and any allergies."},
        {n:"4",icon:"user",   t:"Package redemption logged at checkout",  d:"Client uses session 3 of their 5-session package. The front desk taps Redeem and the package balance updates automatically. Client sees their remaining sessions."},
        {n:"5",icon:"pos",    t:"Billing — retail and add-ons included",  d:"Aromatherapy add-on, the facial serum the client wanted to take home — added to the session bill in one transaction. Receipt sent instantly."},
        {n:"6",icon:"repeat", t:"Renewal nudge fires before expiry",      d:"When a package has one session remaining, EasyGrox sends the client a renewal message automatically. Most clients renew before the final session."},
      ],
      features:[
        {n:"calendar",t:"Room Management",         d:"Each treatment room has its own schedule. Buffer time configured per service type."},
        {n:"gift",    t:"Wellness Packages",        d:"Create and sell multi-session packages. Balance tracked automatically per client."},
        {n:"users",   t:"Therapist Scheduling",     d:"Therapist-specific calendars with specialisation mapping and utilisation tracking."},
        {n:"repeat",  t:"Membership Renewals",      d:"Automatic renewal nudge before expiry. Retain more members with zero manual effort."},
        {n:"user",    t:"Client Preference Notes",  d:"Pressure preference, allergies, focus areas, and oils — stored permanently."},
        {n:"bell",    t:"Session Reminders",        d:"Longer spa sessions get reminders with preparation instructions included."},
        {n:"bar",     t:"Package Analytics",        d:"Redemption rate, renewal rate, revenue from packages vs. walk-in sessions."},
        {n:"globe",   t:"Free Booking Website",     d:"Service menu, therapist profiles, and packages all showcased. Book online 24/7."},
      ],
      staffPoints:["Therapist-specific schedules — each therapist's specialisations drive what clients can book with them","Utilisation rate tracked per therapist — see who has capacity and who is fully booked","Shift scheduling with leave management — leave requests auto-block the therapist calendar","Task assignment for room preparation, product restocking, and client setup before sessions","Separate access levels — therapists see their own schedule, managers see the full picture"],
      clientPoints:["Full client preference profile — pressure, areas, oils, allergies, past session notes","Clients feel deeply known at every visit — even with a different therapist","Package balance visible to client at checkout — they see exactly what is remaining","Monthly wellness clients get a gentle check-in if they miss their usual slot","Membership anniversaries trigger a personalised renewal offer automatically"],
      metrics:[{v:"₹8,400",l:"Avg package revenue per client"},{v:"78%",l:"Package renewal rate"},{v:"94%",l:"Room utilisation"},{v:"4.9★",l:"Avg client rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add spa services with session lengths and room types", where:"App setup · 12 min", done:false},
        {step:"Create therapist profiles and assign specialisations", where:"App setup · 5 min", done:false},
        {step:"Configure treatment rooms and buffer times", where:"App setup · 5 min", done:false},
        {step:"Create wellness packages and membership tiers", where:"App setup · 8 min", done:false},
        {step:"Publish your free spa booking website", where:"One click · instant", done:false},
      ],
      testimonial:{q:"Managing 6 treatment rooms manually was a nightmare. EasyGrox eliminated every double-booking issue we had. The package tracking alone saves my front desk 2 hours a day. Our renewal rate went from 55% to 78%.", n:"Sunita Kapoor", r:"The Wellness Retreat, Bangalore", metric:"Package renewal rate 55% to 78%"},
      faqs:[
        {q:"How does room management work?",a:"When you set up your spa in EasyGrox, you add each treatment room as a resource. When a session is booked, EasyGrox automatically assigns it to an available room and blocks it for the session duration plus your configured buffer time. No two sessions can overlap in the same room."},
        {q:"How are wellness packages tracked?",a:"When a client purchases a package — say 5 deep tissue sessions — EasyGrox creates a package record linked to their client profile. Each time a session is redeemed at the front desk, the balance decrements. The client and your team can always see exactly how many sessions remain."},
        {q:"Can I configure different buffer times for different services?",a:"Yes. You set buffer time per service when you add it in your store setup. A 90-minute hot stone massage might have a 20-minute buffer while a 30-minute express massage has a 10-minute buffer. The calendar enforces these automatically."},
        {q:"How does the membership renewal reminder work?",a:"When a package has one session remaining, EasyGrox triggers an automatic SMS or WhatsApp message to the client encouraging renewal. You can customise the message and include a renewal offer or discount code to incentivise re-purchase."},
      ],
    },
    "Tattoo Studio": {
      sub:"Tattoo Artists · Session Deposits · Artwork History · Multi-Session",
      heroHead:"Manage your artists, protect your deposits, and build every client relationship — session by session.",
      heroSub:"Session booking with deposit capture, client artwork archive, multi-session progress tracking, and a free studio website that showcases every artist's portfolio and live availability.",
      stats:[{v:"100%",l:"Deposits tracked"},{v:"0",l:"Scheduling conflicts"},{v:"24/7",l:"Online enquiry"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"invoice", t:"Deposits collected but never properly tracked",d:"You take a deposit to confirm a session booking. It is written in a notebook or remembered. By appointment day, no one is certain what the deposit amount was or whether it was paid."},
        {icon:"user",    t:"Client artwork references stored everywhere",   d:"Reference images in WhatsApp, design briefs in email, sketches in a folder. When a client comes back for continuation work, piecing together the brief takes 20 minutes."},
        {icon:"calendar",t:"Session scheduling is done on WhatsApp",        d:"Scheduling a sleeve across 4 sessions means 4 separate WhatsApp conversations, manually avoiding overlaps, and hoping nothing gets lost. It always does."},
        {icon:"users",   t:"Walk-ins disrupt in-progress sessions",         d:"An artist is 90 minutes into a detailed piece. A walk-in arrives asking for a small tattoo. Your front desk has no system to manage this without interrupting the session."},
        {icon:"clock",   t:"No buffer between sessions for preparation",    d:"One session ends and the next client arrives in the waiting area before the artist has cleaned up and set up. Rushed transitions affect quality and professionalism."},
        {icon:"layers",  t:"Multi-session progress invisible to everyone",  d:"A client is on session 3 of a sleeve. What was completed in session 2? What is the plan for today? Without a record, every session starts with a memory exercise."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client enquires through your website",  d:"Your studio website shows each artist's portfolio and availability. Clients request a consultation or session booking directly — no DMs to a personal phone."},
        {n:"2",icon:"invoice", t:"Deposit captured at booking",           d:"The booking is confirmed when the deposit is paid. Amount logged against the client record. Deducted from the final bill automatically on session day."},
        {n:"3",icon:"user",    t:"Design brief and references stored",     d:"Artwork references, the agreed design brief, skin tone notes, and placement details — all uploaded to the client profile during or after the consultation."},
        {n:"4",icon:"calendar",t:"Session scheduled with buffer built in",d:"The artist's calendar blocks the session duration plus cleanup buffer. The next booking cannot start until the setup period is complete."},
        {n:"5",icon:"bell",    t:"Session reminder with care instructions",d:"Client gets a reminder 24 hours before with pre-session care instructions — moisturise, eat well, wear appropriate clothing for the placement area."},
        {n:"6",icon:"repeat",  t:"Progress logged after each session",    d:"Artist adds session notes — areas completed, ink used, healing instructions given. The next session brief is built from these notes. No memory required."},
      ],
      features:[
        {n:"invoice", t:"Deposit Management",      d:"Deposit captured and tracked at booking. Deducted from final session bill automatically."},
        {n:"user",    t:"Client Artwork Archive",  d:"References, design briefs, and session notes stored permanently per client."},
        {n:"repeat",  t:"Multi-Session Tracking",  d:"Log progress after each session — areas done, what remains, next session plan."},
        {n:"calendar",t:"Artist Scheduling",       d:"Artist-specific calendars with buffer time between sessions built in automatically."},
        {n:"bell",    t:"Pre-Session Reminders",   d:"Care instructions sent automatically 24 hours before every session."},
        {n:"globe",   t:"Studio Website",          d:"Artist portfolio pages with individual availability. Accept session enquiries 24/7."},
        {n:"bar",     t:"Studio Revenue Tracking", d:"Revenue per artist, per session type. Deposits vs. completions. Monthly summary."},
        {n:"lock",    t:"Session Confidentiality", d:"Each artist sees only their own client records. Studio owner sees everything."},
      ],
      staffPoints:["Each artist has their own calendar, their own client records, and their own portfolio page on your website","Set buffer time per artist — some need 15 minutes setup, others 30. The calendar enforces it","Track revenue per artist, sessions completed per month, and average session value","Artists submit leave requests in the app — their calendar blocks automatically when approved","Role-based access — artists see their own work, studio manager sees all financials"],
      clientPoints:["Full artwork history per client — every session, every reference, every design note","Clients return for continuation work and find their full brief ready — no need to explain again","Pre-session care reminders mean clients arrive prepared — better healing, better results","Deposit tracking means no awkward conversations about what was paid and when","Multi-session clients see their progress tracked — they feel invested in the journey"],
      metrics:[{v:"100%",l:"Deposits tracked automatically"},{v:"4 hrs",l:"Avg session duration managed"},{v:"67%",l:"Multi-session client rate"},{v:"4.8★",l:"Avg studio rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add session types with deposit amounts and durations", where:"App setup · 10 min", done:false},
        {step:"Create artist profiles and upload portfolio images", where:"App setup · 15 min", done:false},
        {step:"Configure session buffers and booking rules", where:"App setup · 5 min", done:false},
        {step:"Publish your studio website with artist profiles", where:"One click · instant", done:false},
        {step:"Share studio booking link on Instagram and social", where:"2 min", done:false},
      ],
      testimonial:{q:"I used to track deposits in a notebook and manage session scheduling across 3 WhatsApp chats. EasyGrox gave every artist their own schedule and every client a proper profile. Deposit disputes went to zero. My artists are happier.", n:"Aryan Mehta", r:"Inkwork Studio, Delhi", metric:"Deposit disputes: zero"},
      faqs:[
        {q:"How does deposit management work?",a:"When you set up a service in EasyGrox, you configure the deposit amount required to confirm a booking. When a client books, the deposit is recorded against their profile. On session day, the front desk sees the deposit paid and it is automatically deducted from the final bill."},
        {q:"How does multi-session progress tracking work?",a:"After each session, the artist opens the client profile and adds session notes — areas completed, ink references, healing instructions given, and the plan for the next session. This builds a complete session history that any team member can access before the next appointment."},
        {q:"Can each artist have their own portfolio page on the studio website?",a:"Yes. Each artist profile you create in EasyGrox appears as their own page on your studio booking website, with a bio, specialisations, and available session slots. Clients can browse artists and book directly with their preferred tattooist."},
        {q:"What happens if a client needs to reschedule a session?",a:"The front desk or the client can reschedule from the booking confirmation. The system updates the artist's calendar, notifies both artist and client, and retains the deposit against the rescheduled session."},
      ],
    },
    "Makeup Artist": {
      sub:"Bridal Sessions · Photoshoots · Kit Inventory · Client Briefs",
      heroHead:"Manage every booking, every brief, and every session — from trial to wedding day.",
      heroSub:"Session scheduling with client brief capture, bridal package management, kit inventory tracking, and a professional booking website that showcases your portfolio and takes bookings while you work.",
      stats:[{v:"100%",l:"Briefs captured"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"},{v:"5 min",l:"Invoice generated"}],
      challenges:[
        {icon:"phone",   t:"Enquiries spread across every platform",      d:"Instagram DMs. WhatsApp messages. Calls. Emails. A bridal enquiry comes in on Tuesday through Instagram and you see it on Thursday. The bride already booked someone else."},
        {icon:"gift",    t:"Bridal sessions are hard to coordinate",      d:"A bridal package has a trial, a mehendi session, and a wedding morning session — possibly for the bride plus bridesmaids. Coordinating these across WhatsApp threads loses details."},
        {icon:"bag",     t:"Kit inventory is managed by memory",          d:"You reach for a foundation shade mid-session and the bottle is empty. There was no alert, no system. You apologise and improvise while the client waits."},
        {icon:"user",    t:"Client skin details live in your head",       d:"Meera has dry skin, uses no fragrance products, and wants a dewy finish. Her skin undertone is warm. If you did not write it down somewhere you can find right now, this information is at risk."},
        {icon:"invoice", t:"Invoicing is done manually after every session",d:"After a full bridal morning you sit down to write an invoice. Then WhatsApp it. Then follow up when it is not paid. This takes time you do not have."},
        {icon:"calendar",t:"Calendar conflicts happen without a system",  d:"You double-book a trial session because your mental calendar and your WhatsApp threads are not the same thing. The client calls to confirm and you have to have an uncomfortable conversation."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client enquires through your website",  d:"Your portfolio website shows your work, packages, and pricing. Clients submit a session enquiry directly from the site — no DMs, no missed messages."},
        {n:"2",icon:"user",    t:"Client brief captured before the session",d:"Before confirming, the client fills a brief — skin type, undertone, allergies, finish preference, occasion, and reference images. All stored permanently in their profile."},
        {n:"3",icon:"calendar",t:"Session added to your calendar",        d:"Trial, pre-wedding, and wedding sessions added as a linked package. Each session blocks your calendar with travel time built in if applicable."},
        {n:"4",icon:"bell",    t:"Pre-session reminders sent automatically",d:"Client receives a reminder 24 hours before with prep instructions — skin care routine to follow, what to avoid, what to wear during the session."},
        {n:"5",icon:"pos",     t:"Invoice generated in 5 minutes",        d:"Session complete. Open the billing screen, confirm services rendered, tap Generate Invoice. Sent to the client via WhatsApp or email. Follow-up automated if unpaid."},
        {n:"6",icon:"tag",     t:"Follow-up and rebooking automated",     d:"Post-session, an automatic thank-you message is sent. If the client is a regular, a rebooking nudge fires at the right interval. Bridal clients receive a review request."},
      ],
      features:[
        {n:"globe",   t:"Portfolio Booking Website", d:"Your work showcased, packages priced, and bookings accepted 24/7. No developer needed."},
        {n:"user",    t:"Client Brief Storage",      d:"Skin type, undertone, allergies, references, and finish preference stored per client."},
        {n:"gift",    t:"Bridal Package Management", d:"Trial, mehendi, wedding sessions — managed as one linked package with individual billing."},
        {n:"bag",     t:"Kit Inventory Tracking",    d:"Track foundations, palettes, and brushes. Low-stock alert before you run out mid-kit."},
        {n:"invoice", t:"Instant Invoice Generation",d:"Professional invoice created in 5 minutes. Sent via WhatsApp. Follow-up automated."},
        {n:"calendar",t:"Session Calendar",          d:"All sessions in one calendar. Travel time built in. No more mental juggling."},
        {n:"bell",    t:"Prep Reminders",            d:"Skin prep instructions sent automatically before every session."},
        {n:"bar",     t:"Revenue by Session Type",   d:"Track bridal vs. editorial vs. event makeup revenue. See which work is most profitable."},
      ],
      staffPoints:["If you work with a team of artists, each has their own calendar and client records","Assign session types per artist — only show a senior artist for bridal, junior for casual sessions","Track revenue per artist, sessions completed, and average client rating this month","Artists can update client briefs and session notes from their own login — no shared passwords","Role-based access means artists see their work and clients, not studio-level financials"],
      clientPoints:["Every client has a permanent brief — their skin, their preferences, their reference images","Returning clients feel remembered and understood from the first sentence of every session","Bridal clients have all three sessions linked — trial notes inform the wedding day approach","Post-session review request builds your portfolio testimonial and Google presence","Client referral tracking helps you understand which clients bring new bookings"],
      metrics:[{v:"₹12,400",l:"Avg bridal package value"},{v:"4.9★",l:"Avg client rating"},{v:"88%",l:"Bridal client return rate"},{v:"24/7",l:"Enquiries captured"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your session types and bridal packages", where:"App setup · 10 min", done:false},
        {step:"Upload portfolio images to your profile", where:"App setup · 10 min", done:false},
        {step:"Set your availability and travel buffer times", where:"App setup · 5 min", done:false},
        {step:"Publish your booking website with portfolio", where:"One click · instant", done:false},
        {step:"Add booking link to Instagram bio and linktree", where:"2 min", done:false},
      ],
      testimonial:{q:"I used to manage bridal bookings across 5 WhatsApp groups. EasyGrox gave me one calendar, one client record per bride, and invoicing that takes 5 minutes. I have not missed a booking enquiry since I launched my website.", n:"Nisha Arora", r:"Studio N Makeup, Chandigarh", metric:"Zero missed enquiries"},
      faqs:[
        {q:"How does bridal package management work?",a:"When you create a bridal package in EasyGrox, you define the sessions it includes — trial, pre-wedding, and wedding day. All three sessions appear as linked bookings in your calendar. Each can be invoiced separately or together. Notes from the trial inform the wedding day session automatically."},
        {q:"Can I capture a client brief before the session?",a:"Yes. Your booking confirmation includes a brief form for the client to complete — skin type, undertone, allergies, finish preference, and reference images. This populates their client profile before you meet them."},
        {q:"How does kit inventory tracking work?",a:"You add every product in your kit to the EasyGrox inventory — foundation shades, palettes, brushes, setting sprays. You set a minimum quantity for each. When a product drops below that level, a low-stock alert appears on your dashboard."},
        {q:"What does the booking website include?",a:"Your EasyGrox booking website includes your portfolio images, a service and package menu with pricing, a session enquiry and booking form, and your availability calendar. Clients can browse your work, understand your pricing, and book or enquire directly — no calls or DMs needed."},
      ],
    },
    "Pet Grooming": {
      sub:"Pet Profiles · Recurring Schedules · Grooming History · Reminders",
      heroHead:"Every pet remembered. Every grooming appointment on time. Every owner delighted.",
      heroSub:"Pet profiles with breed and grooming history, recurring appointment scheduling, automated grooming reminders, retail product tracking, and a free booking website so pet owners can book from their phone.",
      stats:[{v:"100%",l:"Pet profiles tracked"},{v:"6 wk",l:"Auto rebooking cycle"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"paw",     t:"Pet details scattered across notes and memory",  d:"Which shampoo does Bruno react to? What blade length does Coco get? Is Max due for a nail trim as well? The answers are in three different places — or nowhere."},
        {icon:"repeat",  t:"Recurring grooming schedules fall apart",        d:"Goldens need a groom every 6 weeks. You have 40 dogs on irregular schedules and no system to track when each one is due or remind the owner before they forget."},
        {icon:"bag",     t:"Pet products run out without warning",           d:"A dog arrives for a medicated shampoo treatment and the stock ran out 3 days ago. No alert fired. No reorder was made. The owner has to reschedule."},
        {icon:"users",   t:"Walk-in groomings disrupt the whole schedule",   d:"A pet owner walks in with a large dog needing a full groom. Your grooming table is booked. There is no queue system and no way to give an accurate wait time."},
        {icon:"user",    t:"No central record of grooming preferences",      d:"If the regular groomer is off, the replacement has no idea what the pet gets, how they behave, or what the owner prefers. Every session starts from zero."},
        {icon:"bar",     t:"Retail sales tracked on paper or not at all",   d:"Medicated shampoos, conditioners, and grooming accessories are sold at the counter. No inventory record, no stock alert, no monthly retail revenue figure."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Owner books online through your website",d:"Your grooming website shows your services with prices and available slots. Owners select their pet from their profile or create a new pet entry. Booking confirmed in seconds."},
        {n:"2",icon:"user",    t:"Pet profile loaded for the groomer",    d:"Before the appointment, the groomer reviews the pet card — breed, coat type, preferred blade, sensitive areas, past reactions, and any health notes. No surprises."},
        {n:"3",icon:"calendar",t:"Grooming session blocked precisely",    d:"The calendar blocks the right duration — a large dog needs more time than a small one. Service type and breed determine duration. No rushed sessions."},
        {n:"4",icon:"bell",    t:"Reminder sent to the owner",            d:"Owner gets a reminder 24 hours before with preparation tips — do not feed heavily before the appointment, bring vaccination records if required."},
        {n:"5",icon:"pos",     t:"Billing includes grooming plus retail",  d:"Full groom plus the medicated shampoo the owner wanted to take home — one transaction. Inventory deducted. Receipt sent via WhatsApp."},
        {n:"6",icon:"repeat",  t:"Rebooking nudge fires at 6 weeks",      d:"When the grooming cycle for that breed is due, EasyGrox sends the owner a personalised reminder with a direct booking link. The cycle continues automatically."},
      ],
      features:[
        {n:"paw",     t:"Pet Profile System",       d:"Breed, coat, blade preference, health notes, allergies, and full grooming history per pet."},
        {n:"repeat",  t:"Recurring Schedules",      d:"Set a grooming cycle per breed. Automatic rebooking reminder at the right interval."},
        {n:"bell",    t:"Owner Reminders",          d:"24h reminder with prep instructions. Grooming due alerts sent at cycle completion."},
        {n:"bag",     t:"Product Inventory",        d:"Track medicated shampoos, conditioners, and accessories. Low-stock alert before runout."},
        {n:"pos",     t:"Grooming + Retail Billing",d:"Grooming service plus product take-home — one transaction, one receipt."},
        {n:"calendar",t:"Groomer Schedules",        d:"Each groomer has their own calendar. Walk-ins added in 10 seconds to the queue."},
        {n:"bar",     t:"Business Analytics",       d:"Revenue by service, by groomer, by breed. See which services drive the most revenue."},
        {n:"globe",   t:"Free Booking Website",     d:"Pet owners book from their phone at any hour. Your grooming table stays full."},
      ],
      staffPoints:["Each groomer has their own calendar column — their schedule, their pets, their notes","Set service types per groomer — a junior handles small dogs, senior handles large breeds","Track grooming sessions completed, revenue generated, and owner satisfaction per groomer","Leave requests approved in the app — groomer calendar blocked automatically when approved","Walk-in assignments managed from a shared front desk view — no groomer is overloaded"],
      clientPoints:["Every pet has a permanent profile — the whole team knows the dog before it walks through the door","Pet owners feel reassured that their pet is remembered and handled correctly each time","Recurring grooming reminders keep owners returning on schedule without being nagged","Breed-specific notes mean every groomer handles the pet correctly — even if it is a first meeting","Online booking means owners can schedule at midnight — no more playing phone tag"],
      metrics:[{v:"87%",l:"Client rebooking rate"},{v:"6 wks",l:"Avg grooming return cycle"},{v:"₹2,400",l:"Avg monthly spend per pet"},{v:"4.9★",l:"Avg owner rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add grooming services with breed-based durations", where:"App setup · 10 min", done:false},
        {step:"Create groomer profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set grooming cycles per breed type", where:"App setup · 5 min", done:false},
        {step:"Publish your free pet grooming booking website", where:"One click · instant", done:false},
        {step:"Share booking link with existing clients and on social", where:"2 min", done:false},
      ],
      testimonial:{q:"I have 80 regular dogs across 40 families. Keeping track of every pet's grooming history and schedule manually was exhausting. EasyGrox gives every dog their own profile and the auto rebooking reminders have been a revelation. My rebooking rate went from 70% to 87%.", n:"Priya Nair", r:"Pawsome Grooming, Kochi", metric:"Rebooking rate 70% to 87%"},
      faqs:[
        {q:"Can I store different information for each pet?",a:"Yes. Each pet has their own profile with breed, coat type, grooming preferences (blade length, style), health notes, product sensitivities, and a full grooming history. If a pet reacts to a certain shampoo, it is noted in their profile and flagged for every future visit."},
        {q:"How does the recurring grooming reminder work?",a:"When you set up each service, you configure the recommended grooming cycle for that service — 6 weeks for most breeds, 4 weeks for high-maintenance coats. When the cycle period is reached after a completed appointment, EasyGrox automatically sends the owner a personalised reminder with a direct booking link."},
        {q:"Can clients book for multiple pets in one session?",a:"Yes. An owner with two dogs can book separate grooming sessions for each pet in one visit. Each pet has its own service, its own groomer assignment, and its own billing line — but the owner receives one combined receipt."},
        {q:"How does retail inventory tracking work for grooming products?",a:"You add every grooming product to your EasyGrox inventory with a minimum stock level. Every time a product is sold at the POS — medicated shampoo, conditioner, brush — the stock count decrements automatically. When it drops below minimum, an alert appears on your dashboard."},
      ],
    },
  };

  const d = BD[type] || BD["Hair Salon"];

  return (
    <>
      {/* ─── 1. HERO ─── */}
      <section style={{ padding:sm ? "56px 0 44px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"flex", gap:6, alignItems:"center", marginBottom:24, fontFamily:F, fontSize:13, color:C.inkVar, flexWrap:"wrap" }}>
            <button onClick={() => nav("home")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>Home</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <button onClick={() => nav("home")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>By Business</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <span style={{ color:C.teal, fontWeight:600 }}>{type}</span>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 64, alignItems:"center" }}>
            <div>
              <div style={{ display:"flex", alignItems:"center", gap:12, marginBottom:20 }}>
                <div style={{ width:52, height:52, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                  <Ic n={icon} sz={26} col={C.teal} />
                </div>
                <div>
                  <Chip teal sm>{d.sub}</Chip>
                </div>
              </div>
              <H1 style={{ marginBottom:20, lineHeight:1.08 }}>{d.heroHead}</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17.5, marginBottom:32, lineHeight:1.72 }}>{d.heroSub}</Body>
              <div style={{ display:"flex", gap:12, flexWrap:"wrap", marginBottom:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
                <Btn v="outline" onClick={() => nav("productTour")}>Watch Product Tour</Btn>
              </div>
              <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                {["No credit card required","Free booking website","Setup in 30 minutes","Cancel anytime"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px" }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>
            <div>
              <CalendarMock />
              <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr 1fr", gap:10, marginTop:14 }}>
                {d.stats.map((s, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"14px 10px", textAlign:"center", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ fontFamily:F, fontSize:22, fontWeight:800, color:C.teal }}>{s.v}</div>
                    <div style={{ fontFamily:F, fontSize:11, color:C.inkVar, marginTop:3 }}>{s.l}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 2. INDUSTRY CHALLENGES ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <style>{`
          @keyframes fadeUpChallenge {
            from { opacity:0; transform:translateY(28px); }
            to   { opacity:1; transform:translateY(0); }
          }
          @keyframes pulseRing {
            0%,100% { box-shadow: 0 0 0 0 rgba(186,26,26,0.18); }
            50%      { box-shadow: 0 0 0 8px rgba(186,26,26,0); }
          }
          .challenge-card {
            opacity:0;
            animation: fadeUpChallenge 0.52s cubic-bezier(0.22,1,0.36,1) forwards;
          }
          .challenge-card:hover .challenge-icon-ring {
            animation: pulseRing 1.4s ease infinite;
            transform: scale(1.08);
          }
          .challenge-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 36px rgba(186,26,26,0.13) !important;
            border-color: #fca5a5 !important;
          }
          .challenge-icon-ring {
            transition: transform 0.22s ease;
          }
        `}</style>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:48 }}>
            <Label center>We know the daily reality</Label>
            <H2 center>Running a {type} is harder than it looks</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0", fontSize:sm ? 14.5 : 16 }}>These are the operational problems that cost you time, money, and clients — every single week.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:18 }}>
            {d.challenges.map((c, i) => (
              <div key={i} className="challenge-card"
                style={{
                  background:C.surface,
                  borderRadius:R,
                  padding:"28px 22px",
                  border:`1px solid ${C.errBorder}`,
                  boxShadow:C.sh1,
                  transition:"transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease",
                  animationDelay:`${i * 0.09}s`,
                  cursor:"default",
                  position:"relative",
                  overflow:"hidden",
                }}>
                {/* Subtle top accent line */}
                <div style={{ position:"absolute", top:0, left:0, right:0, height:3, background:`linear-gradient(90deg, ${C.err} 0%, #f87171 100%)`, borderRadius:"12px 12px 0 0" }} />
                {/* Icon */}
                <div className="challenge-icon-ring" style={{
                  width:52, height:52, borderRadius:14,
                  background:"#fef2f2",
                  border:"1.5px solid #fecaca",
                  display:"flex", alignItems:"center", justifyContent:"center",
                  marginBottom:18, color:C.err,
                }}>
                  <Ic n={c.icon || "zap"} sz={22} col={C.err} />
                </div>
                {/* Number badge */}
                <div style={{ position:"absolute", top:20, right:20, width:22, height:22, borderRadius:"50%", background:"#fef2f2", border:"1px solid #fecaca", display:"flex", alignItems:"center", justifyContent:"center" }}>
                  <span style={{ fontFamily:F, fontSize:10.5, fontWeight:800, color:C.err }}>{i + 1}</span>
                </div>
                {/* Title */}
                <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{c.t}</div>
                {/* Description */}
                <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.68 }}>{c.d}</div>
              </div>
            ))}
          </div>
          {/* Bottom summary strip */}
          <div style={{ marginTop:36, background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:sm ? "18px 20px" : "20px 28px", display:"flex", flexWrap:"wrap", gap:16, alignItems:"center", justifyContent:"space-between", boxShadow:C.sh1 }}>
            <div style={{ display:"flex", alignItems:"center", gap:12 }}>
              <div style={{ width:36, height:36, borderRadius:10, background:C.errBg, border:`1px solid ${C.errBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                <Ic n="xmark" sz={16} col={C.err} />
              </div>
              <Body sm muted style={{ fontWeight:500 }}>These problems cost the average {type.toLowerCase()} owner <strong style={{ color:C.ink }}>8+ hours per week</strong> in manual admin and missed revenue.</Body>
            </div>
            <Btn sm onClick={() => nav("signup")} style={{ flexShrink:0 }}>See How EasyGrox Fixes This</Btn>
          </div>
        </div>
      </section>

      {/* ─── 3. HOW THE PLATFORM HELPS (Pain vs Gain) ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>The EasyGrox difference</Label>
            <H2 center>What changes when you switch to EasyGrox</H2>
          </div>
          <PainGain
            pains={d.challenges.map(c => c.t + " — " + c.d.split(".")[0])}
            gains={d.features.map(f => f.t + " — " + f.d)}
          />
          <div style={{ textAlign:"center", marginTop:32 }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
          </div>
        </div>
      </section>

      {/* ─── 4. DAILY OPERATIONS WORKFLOW ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>Daily operations workflow</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>How a typical day flows through EasyGrox</H2>
              <Body muted style={{ marginBottom:36, fontSize:sm ? 14.5 : 16 }}>From the first booking to the final payment — every operational step is handled inside one platform. No switching between apps, no lost messages, no manual updates.</Body>
              {d.workflow.map((step, i) => (
                <WorkflowStep key={i} n={step.n} title={step.t} desc={step.d} note={null} final={i === d.workflow.length - 1} />
              ))}
            </div>
            <div style={{ position:md ? "static" : "sticky", top:80 }}>
              <DashMock />
              <div style={{ marginTop:16, background:C.tealLight, borderRadius:R, padding:"16px 20px", border:`1px solid ${C.tealBorder}` }}>
                <div style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal, marginBottom:4 }}>Every step is connected</div>
                <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.6 }}>Bookings, billing, reminders, and follow-up — all in one platform. Your team does not switch apps. Your data does not get lost.</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 5. FEATURE HIGHLIGHTS ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Built for {type}s</Label>
            <H2 center>The features that matter for your business</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0", fontSize:sm ? 14.5 : 16 }}>Not generic software with everything turned on. The features below are precisely what a {type.toLowerCase()} needs — nothing more, nothing irrelevant.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : md ? "repeat(3,1fr)" : "repeat(4,1fr)", gap:14 }}>
            {d.features.map((f, i) => (
              <Card key={i} style={{ padding:"22px 18px", cursor:"default" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:7 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ─── 6. STAFF & TEAM MANAGEMENT ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>Staff and team management</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>Your team runs independently — without you managing every detail</H2>
              <Body muted style={{ marginBottom:32 }}>Give every team member their own login. Let them manage their own schedule, see their clients, and track their performance. You see the full picture from the owner dashboard.</Body>
              {d.staffPoints.map((pt, i) => (
                <div key={i} style={{ display:"flex", gap:12, marginBottom:16, alignItems:"flex-start" }}>
                  <div style={{ width:22, height:22, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body sm muted style={{ lineHeight:1.65 }}>{pt}</Body>
                </div>
              ))}
              <div style={{ marginTop:24 }}>
                <Btn v="outline" onClick={() => nav("staffMgmt")}>Explore Staff Management</Btn>
              </div>
            </div>
            <StaffMock />
          </div>
        </div>
      </section>

      {/* ─── 7. CLIENT / CUSTOMER EXPERIENCE ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <ClientMock />
            <div>
              <Label>Client experience and retention</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>Every client feels remembered — even on their first visit back after months</H2>
              <Body muted style={{ marginBottom:32 }}>A complete client profile built from every visit. Your team knows their history before they sit down. Automated follow-up keeps them returning without any manual effort from you.</Body>
              {d.clientPoints.map((pt, i) => (
                <div key={i} style={{ display:"flex", gap:12, marginBottom:16, alignItems:"flex-start" }}>
                  <div style={{ width:22, height:22, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body sm muted style={{ lineHeight:1.65 }}>{pt}</Body>
                </div>
              ))}
              <div style={{ marginTop:24 }}>
                <Btn v="outline" onClick={() => nav("clients")}>Explore Client Management</Btn>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 8. BUSINESS INSIGHTS & REPORTS ─── */}
      <section style={{ padding:SP, background:C.teal }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:48 }}>
            <Label center light>Business insights and reports</Label>
            <H2 light center style={{ maxWidth:580, margin:"12px auto 14px" }}>Read your entire business in 60 seconds every morning</H2>
            <Body light center style={{ maxWidth:500, margin:"0 auto" }}>Six analytics dashboards — revenue, appointments, staff performance, client behaviour, retail sales, and your booking website. Updated in real time.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:32, alignItems:"center", marginBottom:36 }}>
            <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
              {d.metrics.map((m, i) => (
                <div key={i} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:"22px 18px", border:"1px solid rgba(255,255,255,0.18)", textAlign:"center" }}>
                  <div style={{ fontFamily:F, fontSize:26, fontWeight:800, color:"#fff" }}>{m.v}</div>
                  <div style={{ fontFamily:F, fontSize:12, color:"rgba(255,255,255,0.55)", marginTop:6 }}>{m.l}</div>
                </div>
              ))}
            </div>
            <AnalyticsMock />
          </div>
          <div style={{ textAlign:"center" }}>
            <Btn v="whiteSolid" onClick={() => nav("analytics")}>Explore Analytics</Btn>
          </div>
        </div>
      </section>

      {/* ─── 9. WEBSITE & ONLINE BOOKING ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Free booking website — included</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your {type.toLowerCase()} gets its own booking website — at zero cost</H2>
              <Body muted style={{ fontSize:sm ? 15 : 17, marginBottom:28, lineHeight:1.72 }}>No developer. No hosting fee. No domain purchase. Complete your store setup inside the app and your booking website is ready to publish with one click. Mobile-ready, Google-indexed, accepting bookings 24 hours a day.</Body>
              <div style={{ display:"flex", flexDirection:"column", gap:14, marginBottom:28 }}>
                {[
                  {n:"link",    t:"Free Custom URL",     d:"yourbusiness.easygrox.com — or connect your own domain with one setting."},
                  {n:"shield",  t:"Free Hosting Forever",d:"Hosted on secure servers. Zero cost. Zero renewal fees. Ever."},
                  {n:"refresh", t:"Always Current",      d:"Update a price inside EasyGrox and your website reflects it in seconds."},
                  {n:"bar",     t:"Visitor Analytics",   d:"Traffic sources, booking conversion, and most-viewed services — all tracked automatically."},
                ].map((f, i) => (
                  <div key={i} style={{ display:"flex", gap:12, alignItems:"flex-start" }}>
                    <div style={{ width:36, height:36, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={f.n} sz={16} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.ink, marginBottom:2 }}>{f.t}</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar }}>{f.d}</div>
                    </div>
                  </div>
                ))}
              </div>
              <Btn v="outline" onClick={() => nav("website")}>Explore the Free Website Feature</Btn>
            </div>
            <WebsiteMock />
          </div>
        </div>
      </section>

      {/* ─── 10. MULTI-LOCATION ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Built to scale</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>When you open a second location — or a third — EasyGrox is already ready</H2>
              <Body muted style={{ fontSize:sm ? 15 : 16, marginBottom:28, lineHeight:1.72 }}>The Multi-Location plan gives every branch its own booking website, its own staff and calendar, and its own analytics — all visible from a single owner login. Compare branch performance, share client records, and manage your whole business from one dashboard.</Body>
              <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
                {[
                  {n:"globe",    t:"Website per branch",      d:"Each location gets its own booking website with its own URL."},
                  {n:"bar",      t:"Consolidated analytics",  d:"Total revenue across all branches — or drill into any one."},
                  {n:"users",    t:"Shared client database",  d:"A client who visits two branches has one unified profile."},
                  {n:"lock",     t:"Branch-level access",     d:"Branch managers see their location. You see everything."},
                ].map((f, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"16px 14px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ display:"flex", gap:8, alignItems:"center", marginBottom:8 }}>
                      <Ic n={f.n} sz={16} col={C.teal} />
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{f.t}</div>
                    </div>
                    <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
                <div style={{ background:C.teal, padding:"14px 18px" }}>
                  <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Multi-Location Dashboard</div>
                  <div style={{ color:"rgba(255,255,255,0.55)", fontSize:11, marginTop:2 }}>3 branches · consolidated view</div>
                </div>
                <div style={{ padding:16 }}>
                  {[
                    {loc:"Branch 1 — Jaipur",   rev:"₹48,200", appts:124, rating:"4.9"},
                    {loc:"Branch 2 — Jodhpur",  rev:"₹36,800", appts:98,  rating:"4.7"},
                    {loc:"Branch 3 — Udaipur",  rev:"₹29,400", appts:74,  rating:"4.8"},
                  ].map((b, i) => (
                    <div key={i} style={{ padding:"12px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
                      <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:8 }}>
                        <div style={{ fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink }}>{b.loc}</div>
                        <Chip teal sm>{b.rev}</Chip>
                      </div>
                      <div style={{ display:"flex", gap:16 }}>
                        <div style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>{b.appts} appointments</div>
                        <div style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>★ {b.rating}</div>
                      </div>
                      <div style={{ marginTop:8, background:C.tealLight, borderRadius:9999, height:4, overflow:"hidden" }}>
                        <div style={{ width:`${(parseInt(b.appts)/130)*100}%`, height:"100%", background:C.teal, borderRadius:9999 }} />
                      </div>
                    </div>
                  ))}
                  <div style={{ marginTop:14, padding:"10px 12px", background:C.low, borderRadius:8 }}>
                    <div style={{ display:"flex", justifyContent:"space-between" }}>
                      <span style={{ fontFamily:F, fontSize:12.5, fontWeight:600, color:C.ink }}>Total revenue this month</span>
                      <span style={{ fontFamily:F, fontSize:13, fontWeight:800, color:C.teal }}>₹1,14,400</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 11. BUSINESS ACTIVATION ─── */}
      <section style={{ padding:SP, background:C.tealLight, borderTop:`1px solid ${C.tealBorder}`, borderBottom:`1px solid ${C.tealBorder}` }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Business activation</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your {type.toLowerCase()} is live and taking bookings in under 30 minutes</H2>
              <Body muted style={{ fontSize:sm ? 15 : 16, marginBottom:32, lineHeight:1.72 }}>Create your account here. Complete your store setup inside the app — guided step by step with a progress tracker. Publish your free booking website. Start your first day on EasyGrox.</Body>
              {d.activation.map((s, i) => (
                <div key={i} style={{ display:"flex", gap:14, marginBottom:14, alignItems:"flex-start" }}>
                  <div style={{ width:28, height:28, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`2px solid ${s.done ? C.teal : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    {s.done ? <Ic n="check" sz={13} col="#fff" /> : <span style={{ fontFamily:F, fontSize:11, fontWeight:800, color:C.teal }}>{i+1}</span>}
                  </div>
                  <div>
                    <div style={{ fontFamily:F, fontSize:14.5, fontWeight:s.done ? 700 : 500, color:s.done ? C.teal : C.ink }}>{s.step}</div>
                    <div style={{ fontFamily:F, fontSize:12, color:C.inkVar, marginTop:2 }}>{s.where}</div>
                  </div>
                </div>
              ))}
              <div style={{ marginTop:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account — Start Step 1</Btn>
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, padding:"28px 24px", border:`1px solid ${C.tealBorder}`, boxShadow:C.sh2 }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:18 }}>
                  <H4>Store Setup Progress</H4>
                  <Chip teal>1 of {d.activation.length} done</Chip>
                </div>
                <div style={{ background:C.tealLight, borderRadius:9999, height:8, marginBottom:22, overflow:"hidden" }}>
                  <div style={{ width:`${(1/d.activation.length)*100}%`, height:"100%", background:C.teal, borderRadius:9999, transition:"width .4s" }} />
                </div>
                {d.activation.map((s, i) => (
                  <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"10px 0", borderBottom:i < d.activation.length-1 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center" }}>
                      <div style={{ width:20, height:20, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`1.5px solid ${s.done ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        {s.done && <Ic n="check" sz={11} col="#fff" />}
                      </div>
                      <span style={{ fontFamily:F, fontSize:13.5, color:s.done ? C.teal : C.inkVar, fontWeight:s.done ? 600 : 400 }}>{s.step}</span>
                    </div>
                    {s.done ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:12, color:C.inkSoft }}>{s.where}</span>}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 12. TRUST & TESTIMONIAL ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>From a real {type.toLowerCase()} owner</Label>
            <H2 center>What changes after 30 days on EasyGrox</H2>
          </div>
          <div style={{ maxWidth:720, margin:"0 auto" }}>
            <div style={{ background:C.surface, borderRadius:R, padding:sm ? "28px 22px" : "40px 44px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, borderTop:`4px solid ${C.teal}` }}>
              <div style={{ display:"flex", gap:1, marginBottom:20 }}>
                {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={16} col={C.teal} />)}
              </div>
              <div style={{ background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:R, padding:"10px 16px", marginBottom:24, display:"inline-block" }}>
                <span style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.teal }}>{d.testimonial.metric}</span>
              </div>
              <p style={{ fontFamily:F, fontSize:sm ? 16 : 18, lineHeight:1.8, fontStyle:"italic", color:C.inkVar, marginBottom:28 }}>"{d.testimonial.q}"</p>
              <Divider style={{ marginBottom:22 }} />
              <div style={{ display:"flex", alignItems:"center", gap:14 }}>
                <div style={{ width:44, height:44, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontFamily:F, fontSize:16, fontWeight:800, color:"#fff", flexShrink:0 }}>
                  {d.testimonial.n.split(" ").map(n => n[0]).join("")}
                </div>
                <div>
                  <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink }}>{d.testimonial.n}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, marginTop:2 }}>{d.testimonial.r}</div>
                </div>
              </div>
            </div>
            <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:14, marginTop:16 }}>
              {[
                {v:"10,000+",l:"Beauty businesses on EasyGrox"},
                {v:"4.9/5",  l:"Average platform rating"},
                {v:"30 min", l:"Average setup time"},
              ].map((s, i) => (
                <div key={i} style={{ background:C.surface, borderRadius:R, padding:"18px 14px", border:`1px solid ${C.outlineVar}`, textAlign:"center", boxShadow:C.sh1 }}>
                  <div style={{ fontFamily:F, fontSize:22, fontWeight:800, color:C.teal }}>{s.v}</div>
                  <div style={{ fontFamily:F, fontSize:12, color:C.inkVar, marginTop:4 }}>{s.l}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ─── INDUSTRY FAQ ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1.2fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Label>Questions from {type.toLowerCase()} owners</Label>
              <H2 style={{ marginTop:10, marginBottom:16 }}>Things people ask before signing up</H2>
              <Body muted style={{ marginBottom:24 }}>Our team responds within 24 hours on business days. No bots, no automated queues.</Body>
              <Btn v="outline" onClick={() => nav("contact")}>Ask a Question</Btn>
            </div>
            <div>
              {d.faqs.map((f, i) => (
                <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
                  <button onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"17px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                    <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                    <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
                  </button>
                  {openFaq === i && <div style={{ paddingBottom:18, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.78 }}>{f.a}</div>}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ─── 13. FINAL CTA ─── */}
      <section style={{ padding:sm ? "64px 0" : "96px 0", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Start your free account today</Label>
          <H2 light center style={{ maxWidth:600, margin:"12px auto 18px" }}>Your {type.toLowerCase()} deserves a platform that understands how it actually operates.</H2>
          <Body light center style={{ maxWidth:480, margin:"0 auto 36px" }}>Create your free account. Set up your store inside the app. Publish your free booking website. Start managing smarter — in under 30 minutes.</Body>
          <div style={{ display:"flex", gap:14, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("productTour")}>Watch Product Tour</Btn>
          </div>
          <div style={{ display:"flex", gap:16, justifyContent:"center", flexWrap:"wrap", marginTop:22 }}>
            {["No credit card required","Free booking website on every plan","Setup in 30 minutes","Cancel anytime"].map((t, i) => (
              <span key={i} style={{ fontFamily:F, fontSize:12.5, color:"rgba(255,255,255,0.55)", display:"flex", alignItems:"center", gap:5 }}>
                <Ic n="check" sz={11} col="rgba(255,255,255,0.55)" />{t}
              </span>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
