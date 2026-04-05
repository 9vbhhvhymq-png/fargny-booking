var h = React.createElement;
var useState = React.useState;
var useEffect = React.useEffect;
var useCallback = React.useCallback;
var Fragment = React.Fragment;

// ═══════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════
var FAMILY_BRANCHES = [
  { id: 1, name: "Fromageot", color: "#B85042" },
  { id: 2, name: "Van der Grinten", color: "#4A7C59" },
  { id: 3, name: "Stam 3", color: "#3B6B9E" },
  { id: 4, name: "Stam 4", color: "#C4853B" },
  { id: 5, name: "Stam 5", color: "#7B5EA7" },
  { id: 6, name: "Stam 6", color: "#2E8B8B" },
  { id: 7, name: "Stam 7", color: "#C75B7A" },
  { id: 8, name: "Stam 8", color: "#5B7553" },
  { id: 9, name: "Stam 9", color: "#8B6F47" },
];

var PHASES = {
  clan: { id: "clan", icon: "\u{1F3E0}", revealable: true },
  priority: { id: "priority", icon: "\u2B50", revealable: true },
  regular: { id: "regular", icon: "\u{1F33F}", revealable: false },
};

var COLORS = {
  bg: '#F5F0EB', text: '#2C1810', muted: '#8B7D6B', border: '#E0D8CF',
  white: '#FFFFFF', accent: '#B85042', success: '#4A7C59', warning: '#C4853B', danger: '#B85042',
};

var PHASE_ORDER = ['clan', 'priority', 'regular'];

var RATES = { child04: 0, child59: 5, adult: 10 };
var HOUSE_FEE = 50;
var CLEANING_FEE = 70;

// ═══════════════════════════════════════════════════════
// TRANSLATIONS
// ═══════════════════════════════════════════════════════
var T = {
  en: {
    appName:"Fargny",subtitle:"Holiday House Booking",signIn:"Sign In",register:"Register",email:"Email address",displayName:"Display name",password:"Password",fillAll:"Please fill in all fields",invalidEmail:"Please enter a valid email",pickBranch:"Please select your family branch",whichBranch:"Which family branch do you belong to?",createAccount:"Create Account",nameTaken:"Email already registered",invalidLogin:"Invalid email or password",signOut:"Sign Out",weeksBooked:"weeks booked",book:"Book",calendar:"Calendar",statistics:"Statistics",bookingPhase:"Booking Phase",year:"Year",myBookings:"My Bookings",noBookingsYet:"No bookings yet",familyBranches:"Family Branches",clickBranch:"Click a branch to see its members",all:"All",noWeeks:"No weeks available",bookBtn:"Book",cancelBtn:"Cancel",yourBooking:"Your booking",branchAlreadyBooked:"Your branch already booked a week",youAlreadyBooked:"You already booked a week",booked:"Booked",bookingCancelled:"Booking cancelled",available:"Available",doubleBooking:"Double booking",noMembers:"No members registered yet",membersRegistered:"members registered",memberRegistered:"member registered",close:"Close",admin:"Admin",adminTitle:"Admin Panel",adminDesc:"Manage bookings, cancellations & payments",owner:"Owner",pickOwner:"Choose an owner...",phase:"Phase",week:"Week",pickWeek:"Choose a week...",clanName:"Clan Booking",clanDesc:"Each family branch picks one week",clanRule:"One week per family branch",priorityName:"Priority Booking",priorityDesc:"Each owner picks one week",priorityRule:"One week per owner",regularName:"Regular Booking",regularDesc:"Book as much as you like",regularRule:"Bookable up to 6 months ahead",revealed:"Revealed",hidden:"Hidden until reveal",hiddenCalNote:"Clan and Priority bookings are not yet revealed. Only your own and Regular bookings are shown.",adminBooking:"via admin",bookOnBehalf:"Book on behalf",loading:"Loading...",emailTab:"Paste Email",manualTab:"Manual",pasteEmail:"Paste the email here...",parseEmail:"Parse Email",parsing:"Reading email...",parsedResult:"Parsed from email",noMatch:"Could not match a registered owner",weekNotFound:"Could not identify the requested week",parseError:"Could not parse the email. Please use manual entry.",confirmBooking:"Confirm Booking",parsedName:"Detected name",parsedWeek:"Requested week",feedback:"Feedback",surveyTitle:"Share your feedback",surveyIntro:"Help us improve the booking system. Your answers are anonymous.",fq1:"What features would you like to see added?",fq1placeholder:"Describe features you'd like...",fq2:"What would you change about the current design?",fq2placeholder:"Describe changes you'd suggest...",fq3:"How easy was it to navigate the booking system?",fq3a:"Very easy",fq3b:"Easy",fq3c:"Neutral",fq3d:"Difficult",fq3e:"Very difficult",fq4:"Any other comments or suggestions?",fq4placeholder:"Type your comments here...",submitFeedback:"Submit Feedback",feedbackThanks:"Thank you for your feedback!",feedbackSaved:"Your response has been saved.",submitAnother:"Submit another response",feedbackCount:"responses collected",downloadFeedback:"Download Feedback (Excel)",phaseOpen:"Open",phaseTo:"to",phaseRevealed:"Bookings will be revealed on",phaseVisibleNow:"Bookings are visible immediately",phaseOpensOn:"Opens",phaseClosed:"Closed",phaseRevealedOn:"Bookings were revealed on",regularOpensInfo:"Opens 1 Jan, bookable up to 6 months ahead",weekNotYetOpen:"Opens for booking on",weekBookedByOther:"Already booked",confirmationEmailSent:"A confirmation email will be sent to",requestCancel:"Request cancellation",pendingCancellation:"Cancellation pending",cancelRequested:"Cancellation requested",approveCancellation:"Approve",rejectCancellation:"Reject",pendingCancellations:"Pending Cancellations",cancellationsTab:"Cancellations",paymentsTab:"Payments",cancellationApproved:"Cancellation approved",cancellationRejected:"Cancellation rejected",noPendingCancellations:"No pending cancellations",statsTitle:"Booking Statistics",bookingsPerUser:"Bookings per user",bookingsPerBranch:"Bookings per branch",overallOccupancy:"Overall occupancy",bookingTimeline:"Booking timeline",totalWeeks:"Total weeks",bookedWeeks:"Booked weeks",availableWeeks:"Available weeks",occupancyRate:"Occupancy rate",userName:"User",branchLabel:"Branch",total:"Total",lastLogin:"Last login",bookingCount:"Bookings",adminOnly:"Admin access required",noData:"No data available",mon:"Mon",tue:"Tue",wed:"Wed",thu:"Thu",fri:"Fri",sat:"Sat",sun:"Sun",active:"Active",opens:"Opens",closed:"Closed",
    costs:"Costs",houseFee:"House fee",perNight:"per night",perPerson:"Per person",cleaningFee:"Cleaning fee",totalCost:"Total",notPaid:"Not paid",invoiceSent:"Invoice sent",paid:"Paid",transferTo:"Transfer to",questionsCall:"Questions? Call",child04:"Children 0\u20134 (free)",child59:"Children 5\u20139",adult:"10 years and older",nights:"nights",nightLabel:"Night",saveCosts:"Save",costSummary:"Cost summary",paymentStatus:"Payment status",paymentOverview:"Payment overview",amount:"Amount",filterUnpaid:"Show unpaid only",allPayments:"All",whatsappHelp:"Need help?",bankInstructions:"Transfer the amount to penningmeester@fargny.org \u2014 you will receive an invoice with payment request. Questions? Call Rogier: +31-6-57711402",
  },
  nl: {
    appName:"Fargny",subtitle:"Vakantiehuis Boekingssysteem",signIn:"Inloggen",register:"Registreren",email:"E-mailadres",displayName:"Weergavenaam",password:"Wachtwoord",fillAll:"Vul alle velden in",invalidEmail:"Voer een geldig e-mailadres in",pickBranch:"Kies je stam",whichBranch:"Bij welke stam hoor je?",createAccount:"Account aanmaken",nameTaken:"E-mail al geregistreerd",invalidLogin:"Ongeldig e-mailadres of wachtwoord",signOut:"Uitloggen",weeksBooked:"weken geboekt",book:"Boeken",calendar:"Kalender",statistics:"Statistieken",bookingPhase:"Boekingsfase",year:"Jaar",myBookings:"Mijn Boekingen",noBookingsYet:"Nog geen boekingen",familyBranches:"Stammen",clickBranch:"Klik op een stam om de leden te zien",all:"Alle",noWeeks:"Geen weken beschikbaar",bookBtn:"Boeken",cancelBtn:"Annuleren",yourBooking:"Jouw boeking",branchAlreadyBooked:"Je stam heeft al een week geboekt",youAlreadyBooked:"Je hebt al een week geboekt",booked:"Geboekt",bookingCancelled:"Boeking geannuleerd",available:"Beschikbaar",doubleBooking:"Dubbele boeking",noMembers:"Nog geen leden geregistreerd",membersRegistered:"leden geregistreerd",memberRegistered:"lid geregistreerd",close:"Sluiten",admin:"Beheerder",adminTitle:"Beheerderspaneel",adminDesc:"Beheer boekingen, annuleringen & betalingen",owner:"Eigenaar",pickOwner:"Kies een eigenaar...",phase:"Fase",week:"Week",pickWeek:"Kies een week...",clanName:"Stamboeking",clanDesc:"Elke stam kiest een week",clanRule:"E\u00e9n week per stam",priorityName:"Prioriteitsboeking",priorityDesc:"Elke eigenaar kiest een week",priorityRule:"E\u00e9n week per eigenaar",regularName:"Reguliere Boeking",regularDesc:"Boek zoveel als je wilt",regularRule:"Boekbaar tot 6 maanden vooruit",revealed:"Onthuld",hidden:"Verborgen tot onthulling",hiddenCalNote:"Stam- en prioriteitsboekingen zijn nog niet onthuld. Alleen je eigen en reguliere boekingen worden getoond.",adminBooking:"via beheerder",bookOnBehalf:"Namens iemand boeken",loading:"Laden...",emailTab:"Email plakken",manualTab:"Handmatig",pasteEmail:"Plak hier de email...",parseEmail:"Email lezen",parsing:"Email wordt gelezen...",parsedResult:"Uit email gehaald",noMatch:"Kon geen geregistreerde eigenaar vinden",weekNotFound:"Kon de gevraagde week niet herkennen",parseError:"Kon de email niet verwerken.",confirmBooking:"Boeking bevestigen",parsedName:"Herkende naam",parsedWeek:"Gevraagde week",feedback:"Feedback",surveyTitle:"Deel je feedback",surveyIntro:"Help ons het boekingssysteem te verbeteren. Je antwoorden zijn anoniem.",fq1:"Welke functies zou je graag toegevoegd zien?",fq1placeholder:"Beschrijf gewenste functies...",fq2:"Wat zou je veranderen aan het huidige ontwerp?",fq2placeholder:"Beschrijf suggesties voor wijzigingen...",fq3:"Hoe makkelijk was het om door het boekingssysteem te navigeren?",fq3a:"Zeer makkelijk",fq3b:"Makkelijk",fq3c:"Neutraal",fq3d:"Moeilijk",fq3e:"Zeer moeilijk",fq4:"Overige opmerkingen of suggesties?",fq4placeholder:"Typ hier je opmerkingen...",submitFeedback:"Feedback versturen",feedbackThanks:"Bedankt voor je feedback!",feedbackSaved:"Je reactie is opgeslagen.",submitAnother:"Nog een reactie versturen",feedbackCount:"reacties verzameld",downloadFeedback:"Feedback downloaden (Excel)",phaseOpen:"Open",phaseTo:"tot",phaseRevealed:"Boekingen worden onthuld op",phaseVisibleNow:"Boekingen zijn direct zichtbaar",phaseOpensOn:"Opent",phaseClosed:"Gesloten",phaseRevealedOn:"Boekingen werden onthuld op",regularOpensInfo:"Opent 1 jan, boekbaar tot 6 maanden vooruit",weekNotYetOpen:"Opent voor boeking op",weekBookedByOther:"Al geboekt",confirmationEmailSent:"Er wordt een bevestigingsmail gestuurd naar",requestCancel:"Annulering aanvragen",pendingCancellation:"Annulering in behandeling",cancelRequested:"Annulering aangevraagd",approveCancellation:"Goedkeuren",rejectCancellation:"Afwijzen",pendingCancellations:"Openstaande annuleringen",cancellationsTab:"Annuleringen",paymentsTab:"Betalingen",cancellationApproved:"Annulering goedgekeurd",cancellationRejected:"Annulering afgewezen",noPendingCancellations:"Geen openstaande annuleringen",statsTitle:"Boekingsstatistieken",bookingsPerUser:"Boekingen per gebruiker",bookingsPerBranch:"Boekingen per stam",overallOccupancy:"Totale bezetting",bookingTimeline:"Boekingstijdlijn",totalWeeks:"Totaal weken",bookedWeeks:"Geboekte weken",availableWeeks:"Beschikbare weken",occupancyRate:"Bezettingsgraad",userName:"Gebruiker",branchLabel:"Stam",total:"Totaal",lastLogin:"Laatste login",bookingCount:"Boekingen",adminOnly:"Beheerdertoegang vereist",noData:"Geen gegevens beschikbaar",mon:"Ma",tue:"Di",wed:"Wo",thu:"Do",fri:"Vr",sat:"Za",sun:"Zo",active:"Actief",opens:"Opent",closed:"Gesloten",
    costs:"Kosten",houseFee:"Huiskosten",perNight:"per nacht",perPerson:"Per persoon",cleaningFee:"Schoonmaakkosten",totalCost:"Totaal",notPaid:"Niet betaald",invoiceSent:"Factuur verstuurd",paid:"Betaald",transferTo:"Overmaken naar",questionsCall:"Vragen? Bel",child04:"Kinderen 0\u20134 (gratis)",child59:"Kinderen 5\u20139",adult:"10 jaar en ouder",nights:"nachten",nightLabel:"Nacht",saveCosts:"Opslaan",costSummary:"Kostenoverzicht",paymentStatus:"Betaalstatus",paymentOverview:"Betalingsoverzicht",amount:"Bedrag",filterUnpaid:"Alleen onbetaald",allPayments:"Alle",whatsappHelp:"Hulp nodig?",bankInstructions:"Maak het bedrag over naar penningmeester@fargny.org \u2014 je ontvangt een factuur met betaalverzoek. Vragen? Bel Rogier: +31-6-57711402",
  },
};

// ═══════════════════════════════════════════════════════
// WEEK UTILS
// ═══════════════════════════════════════════════════════
var MONTHS_SHORT = {
  en: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
  nl: ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
};
var FULL_MONTHS = {
  en: ['January','February','March','April','May','June','July','August','September','October','November','December'],
  nl: ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'],
};

function toISO(date) {
  return date.getFullYear()+'-'+String(date.getMonth()+1).padStart(2,'0')+'-'+String(date.getDate()).padStart(2,'0');
}
function parseISO(s) { var p=s.split('-'); return new Date(+p[0],+p[1]-1,+p[2]); }

function generateWeeks(year) {
  var weeks=[], d=new Date(year,0,1);
  while(d.getDay()!==6) d.setDate(d.getDate()+1);
  var weekNum=1;
  while(d.getFullYear()<=year){
    var start=new Date(d), end=new Date(d); end.setDate(end.getDate()+6);
    if(start.getFullYear()!==year) break;
    weeks.push({id:year+'-W'+String(weekNum).padStart(2,'0'),weekNum:weekNum,start:toISO(start),end:toISO(end),month:start.getMonth()});
    d.setDate(d.getDate()+7); weekNum++;
  }
  return weeks;
}

function formatDate(iso,lang){if(!iso)return'';var p=iso.split('-');return parseInt(p[2])+' '+MONTHS_SHORT[lang][parseInt(p[1])-1];}
function formatFullDate(iso,lang){if(!iso)return'';var p=iso.split('-');return parseInt(p[2])+' '+MONTHS_SHORT[lang][parseInt(p[1])-1]+' '+p[0];}

function isWeekBookableRegular(weekStart){
  var today=new Date();today.setHours(0,0,0,0);
  var start=parseISO(weekStart);
  var sixBefore=new Date(start);sixBefore.setMonth(sixBefore.getMonth()-6);
  return today>=sixBefore;
}
function regularOpenDate(weekStart){
  var start=parseISO(weekStart);
  var sixBefore=new Date(start);sixBefore.setMonth(sixBefore.getMonth()-6);
  return toISO(sixBefore);
}

function getWeekNights(weekStart){
  var d=parseISO(weekStart), nights=[];
  for(var i=0;i<7;i++){var nd=new Date(d);nd.setDate(nd.getDate()+i);nights.push(toISO(nd));}
  return nights;
}

// ═══════════════════════════════════════════════════════
// MOCK DATA
// ═══════════════════════════════════════════════════════
var MOCK_USERS = [
  { id:1, name:"Marielle", email:"marielle@fargny.nl", branch_id:1, is_admin:true },
  { id:2, name:"Jean-Pierre Fromageot", email:"jp@fargny.nl", branch_id:1, is_admin:false },
  { id:3, name:"Sophie Van der Grinten", email:"sophie@fargny.nl", branch_id:2, is_admin:false },
  { id:4, name:"Thomas Van der Grinten", email:"thomas@fargny.nl", branch_id:2, is_admin:false },
  { id:5, name:"Anna Stam3", email:"anna@fargny.nl", branch_id:3, is_admin:false },
  { id:6, name:"Peter Stam4", email:"peter@fargny.nl", branch_id:4, is_admin:false },
];

var MOCK_PHASE_CONFIG = {
  year:2026,
  clan_start:'2025-11-01', clan_end:'2025-12-15', clan_reveal:'2025-12-16',
  priority_start:'2025-12-15', priority_end:'2025-12-31', priority_reveal:'2026-01-01',
  regular_start:'2026-01-01',
};

var INITIAL_BOOKINGS = [
  { id:101, week_id:'2026-W12', year:2026, user_id:3, branch_id:2, phase:'clan', admin_booked:false, user_name:'Sophie Van der Grinten', user_email:'sophie@fargny.nl', branch_name:'Van der Grinten', status:'confirmed', created_at:'2025-11-05', payment_status:'paid', guests:null },
  { id:102, week_id:'2026-W28', year:2026, user_id:2, branch_id:1, phase:'clan', admin_booked:false, user_name:'Jean-Pierre Fromageot', user_email:'jp@fargny.nl', branch_name:'Fromageot', status:'confirmed', created_at:'2025-11-10', payment_status:'invoice_sent', guests:null },
  { id:103, week_id:'2026-W30', year:2026, user_id:3, branch_id:2, phase:'priority', admin_booked:false, user_name:'Sophie Van der Grinten', user_email:'sophie@fargny.nl', branch_name:'Van der Grinten', status:'confirmed', created_at:'2025-12-18', payment_status:'not_paid', guests:null },
  { id:104, week_id:'2026-W35', year:2026, user_id:5, branch_id:3, phase:'regular', admin_booked:false, user_name:'Anna Stam3', user_email:'anna@fargny.nl', branch_name:'Stam 3', status:'confirmed', created_at:'2026-02-15', payment_status:'not_paid', guests:null },
  { id:105, week_id:'2026-W35', year:2026, user_id:6, branch_id:4, phase:'regular', admin_booked:true, user_name:'Peter Stam4', user_email:'peter@fargny.nl', branch_name:'Stam 4', status:'confirmed', created_at:'2026-02-20', payment_status:'paid', guests:null },
];

var MOCK_BRANCHES = FAMILY_BRANCHES.map(function(b){
  return Object.assign({},b,{member_count:MOCK_USERS.filter(function(u){return u.branch_id===b.id;}).length});
});

// ═══════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════
function getPhaseMeta(phase,t){
  return {
    clan:{name:t.clanName,desc:t.clanDesc,rule:t.clanRule,icon:PHASES.clan.icon},
    priority:{name:t.priorityName,desc:t.priorityDesc,rule:t.priorityRule,icon:PHASES.priority.icon},
    regular:{name:t.regularName,desc:t.regularDesc,rule:t.regularRule,icon:PHASES.regular.icon},
  }[phase];
}
function phaseColor(p){return p==='clan'?'#B85042':p==='priority'?'#C4853B':'#4A7C59';}
function getPhaseName(p,t){return{clan:t.clanName,priority:t.priorityName,regular:t.regularName}[p]||p;}

function getPhaseStatus(phaseId, cfg){
  if(!cfg) return 'active';
  var today=toISO(new Date());
  if(phaseId==='clan'){
    if(today<cfg.clan_start) return 'upcoming';
    if(today<=cfg.clan_end) return 'active';
    return 'closed';
  }
  if(phaseId==='priority'){
    if(today<cfg.priority_start) return 'upcoming';
    if(today<=cfg.priority_end) return 'active';
    return 'closed';
  }
  if(today<cfg.regular_start) return 'upcoming';
  return 'active';
}

function getPhaseWindow(phaseId, cfg, lang){
  if(!cfg) return '';
  if(phaseId==='clan') return formatDate(cfg.clan_start,lang)+' \u2013 '+formatDate(cfg.clan_end,lang);
  if(phaseId==='priority') return formatDate(cfg.priority_start,lang)+' \u2013 '+formatDate(cfg.priority_end,lang);
  return (lang==='nl'?'Opent 1 jan, boekbaar tot 6 maanden vooruit':'Opens 1 Jan, bookable up to 6 months ahead');
}

function shortName(fullName){
  var parts=fullName.trim().split(/\s+/);
  if(parts.length===1) return parts[0];
  return parts[0]+' '+parts[parts.length-1].charAt(0)+'.';
}

function calcBookingCost(guests, weekStart){
  if(!guests||!weekStart) return {house:0,person:0,cleaning:CLEANING_FEE,total:CLEANING_FEE,nightsStayed:0};
  var nightsWithPeople=0, personFee=0;
  for(var n=0;n<7;n++){
    var c04=guests.child04[n]||0, c59=guests.child59[n]||0, ad=guests.adult[n]||0;
    if(c04+c59+ad>0) nightsWithPeople++;
    personFee += c59*RATES.child59 + ad*RATES.adult;
  }
  var house=HOUSE_FEE*nightsWithPeople;
  return {house:house,person:personFee,cleaning:CLEANING_FEE,total:house+personFee+CLEANING_FEE,nightsStayed:nightsWithPeople};
}

function paymentBadge(status,t){
  var map={not_paid:{label:t.notPaid,color:'#DC2626',bg:'#FEF2F2'},invoice_sent:{label:t.invoiceSent,color:'#D97706',bg:'#FFFBEB'},paid:{label:t.paid,color:'#059669',bg:'#ECFDF5'}};
  var s=map[status]||map.not_paid;
  return h('span',{style:{fontSize:10,fontWeight:700,padding:'2px 8px',borderRadius:10,background:s.bg,color:s.color,whiteSpace:'nowrap'}},s.label);
}

// ═══════════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════════
function Toast(p){
  if(!p.message) return null;
  return h('div',{style:{position:'fixed',top:20,right:20,background:COLORS.text,color:COLORS.white,padding:'12px 24px',borderRadius:10,fontSize:14,fontWeight:500,zIndex:1000,boxShadow:'0 4px 20px rgba(0,0,0,0.15)',animation:'fadeIn 0.3s ease',fontFamily:"'DM Sans',sans-serif",maxWidth:400,whiteSpace:'pre-line'}},p.message);
}

// ═══════════════════════════════════════════════════════
// BRANCH MEMBERS PANEL
// ═══════════════════════════════════════════════════════
function BranchMembersPanel(p){
  var branch=p.branch,onClose=p.onClose,t=p.t;
  var members=MOCK_USERS.filter(function(u){return u.branch_id===branch.id;});
  return h('div',{style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'},onClick:onClose},
    h('div',{onClick:function(e){e.stopPropagation();},style:{background:COLORS.white,borderRadius:16,padding:28,width:360,maxHeight:'70vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'}},
      h('div',{style:{display:'flex',alignItems:'center',gap:10,marginBottom:20}},
        h('div',{style:{width:16,height:16,borderRadius:4,background:branch.color}}),
        h('h3',{style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display',Georgia,serif"}},branch.name)
      ),
      members.length===0?h('p',{style:{fontSize:14,color:'#B0A090',margin:0}},t.noMembers)
      :h('div',{style:{display:'flex',flexDirection:'column',gap:8}},members.map(function(m){
        return h('div',{key:m.id,style:{display:'flex',alignItems:'center',gap:10,padding:'10px 14px',background:'#F8F5F2',borderRadius:10,borderLeft:'3px solid '+branch.color}},
          h('div',{style:{width:32,height:32,borderRadius:'50%',background:branch.color+'20',display:'flex',alignItems:'center',justifyContent:'center',fontSize:14,fontWeight:700,color:branch.color}},m.name.charAt(0).toUpperCase()),
          h('div',null,h('span',{style:{fontSize:14,fontWeight:500,color:COLORS.text}},m.name),h('br'),h('span',{style:{fontSize:11,color:COLORS.muted}},m.email),
            m.is_admin&&h('span',{style:{fontSize:10,background:'#C4853B20',color:'#C4853B',padding:'2px 6px',borderRadius:4,marginLeft:6,fontWeight:600}},t.admin)));})),
      h('div',{style:{marginTop:16,fontSize:12,color:COLORS.muted}},members.length+' '+(members.length!==1?t.membersRegistered:t.memberRegistered)),
      h('button',{onClick:onClose,style:{marginTop:16,width:'100%',padding:'10px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.close)));
}

// ═══════════════════════════════════════════════════════
// COST CALCULATOR MODAL
// ═══════════════════════════════════════════════════════
function CostCalculator(p){
  var booking=p.booking,weeks=p.weeks,onSave=p.onSave,onClose=p.onClose,t=p.t,lang=p.lang;
  var w=weeks.find(function(wk){return wk.id===booking.week_id;});
  var dayLabels=[t.sat,t.sun,t.mon,t.tue,t.wed,t.thu,t.fri];
  var initGuests=booking.guests||{child04:[0,0,0,0,0,0,0],child59:[0,0,0,0,0,0,0],adult:[0,0,0,0,0,0,0]};
  var _g=useState(function(){return JSON.parse(JSON.stringify(initGuests));}),guests=_g[0],setGuests=_g[1];
  var setCell=function(row,col,val){var v=Math.max(0,parseInt(val)||0);setGuests(function(prev){var next=JSON.parse(JSON.stringify(prev));next[row][col]=v;return next;});};
  var cost=calcBookingCost(guests,w?w.start:null);
  var cellStyle={width:42,padding:'6px 2px',border:'1px solid '+COLORS.border,borderRadius:4,fontSize:13,textAlign:'center',outline:'none',fontFamily:"'DM Sans',sans-serif"};

  return h('div',{style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:300,display:'flex',alignItems:'center',justifyContent:'center'},onClick:onClose},
    h('div',{onClick:function(e){e.stopPropagation();},style:{background:COLORS.white,borderRadius:16,padding:28,width:580,maxHeight:'90vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'}},
      h('h3',{style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:'0 0 4px 0',fontFamily:"'Playfair Display',Georgia,serif"}},'\u{1F4B0} '+t.costs),
      w&&h('p',{style:{fontSize:13,color:COLORS.muted,margin:'0 0 16px 0'}},'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang)),
      h('div',{style:{overflowX:'auto',marginBottom:16}},
        h('table',{style:{borderCollapse:'collapse',width:'100%',fontSize:12}},
          h('thead',null,h('tr',null,h('th',{style:{textAlign:'left',padding:'6px 8px',fontSize:11,color:COLORS.muted}},''),
            dayLabels.map(function(d,i){return h('th',{key:i,style:{padding:'6px 4px',fontSize:11,color:COLORS.muted,textAlign:'center',fontWeight:600}},d);}))),
          h('tbody',null,
            [{key:'child04',label:t.child04,rate:'\u20AC0'},{key:'child59',label:t.child59,rate:'\u20AC5'},{key:'adult',label:t.adult,rate:'\u20AC10'}].map(function(row){
              return h('tr',{key:row.key},
                h('td',{style:{padding:'6px 8px',fontSize:12,fontWeight:500,color:COLORS.text,whiteSpace:'nowrap'}},row.label,' ',h('span',{style:{color:COLORS.muted,fontSize:10}},row.rate+'/'+t.nightLabel.toLowerCase())),
                [0,1,2,3,4,5,6].map(function(col){return h('td',{key:col,style:{padding:'4px 2px',textAlign:'center'}},
                  h('input',{type:'number',min:0,value:guests[row.key][col],onChange:function(e){setCell(row.key,col,e.target.value);},style:cellStyle}));}));
            })))),
      h('div',{style:{background:'#F8F5F2',borderRadius:12,padding:16,marginBottom:16}},
        h('div',{style:{fontSize:12,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',marginBottom:10}},t.costSummary),
        h('div',{style:{display:'flex',flexDirection:'column',gap:6}},
          h('div',{style:{display:'flex',justifyContent:'space-between',fontSize:13}},h('span',null,t.houseFee+' (\u20AC'+HOUSE_FEE+' \u00D7 '+cost.nightsStayed+' '+t.nights+')'),h('span',{style:{fontWeight:600}},'\u20AC'+cost.house.toFixed(2))),
          h('div',{style:{display:'flex',justifyContent:'space-between',fontSize:13}},h('span',null,t.perPerson),h('span',{style:{fontWeight:600}},'\u20AC'+cost.person.toFixed(2))),
          h('div',{style:{display:'flex',justifyContent:'space-between',fontSize:13}},h('span',null,t.cleaningFee),h('span',{style:{fontWeight:600}},'\u20AC'+CLEANING_FEE.toFixed(2))),
          h('div',{style:{display:'flex',justifyContent:'space-between',fontSize:15,fontWeight:700,color:COLORS.text,borderTop:'1px solid '+COLORS.border,paddingTop:8,marginTop:4}},h('span',null,t.totalCost),h('span',null,'\u20AC'+cost.total.toFixed(2))))),
      h('div',{style:{background:'#3B6B9E0A',border:'1px solid #3B6B9E20',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#3B6B9E',lineHeight:1.5,marginBottom:16}},t.bankInstructions),
      h('div',{style:{display:'flex',gap:8}},
        h('button',{onClick:onClose,style:{flex:1,padding:'11px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.close),
        h('button',{onClick:function(){onSave(booking.id,guests,cost.total);},style:{flex:1,padding:'11px 0',background:COLORS.success,border:'none',borderRadius:10,fontSize:14,fontWeight:600,color:'white',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.saveCosts))));
}

// ═══════════════════════════════════════════════════════
// ADMIN PANEL
// ═══════════════════════════════════════════════════════
function AdminPanel(p){
  var users=p.users,weeks=p.weeks,year=p.year,bookings=p.bookings,onBooked=p.onBooked,onApproveCancellation=p.onApproveCancellation,onRejectCancellation=p.onRejectCancellation,onUpdatePayment=p.onUpdatePayment,onClose=p.onClose,t=p.t,lang=p.lang;
  var _tab=useState('manual'),tab=_tab[0],setTab=_tab[1];
  var _su=useState(''),selUser=_su[0],setSelUser=_su[1];
  var _sw=useState(''),selWeek=_sw[0],setSelWeek=_sw[1];
  var _sp=useState('regular'),selPhase=_sp[0],setSelPhase=_sp[1];
  var _pf=useState(false),unpaidOnly=_pf[0],setUnpaidOnly=_pf[1];
  var pending=(bookings||[]).filter(function(b){return b.status==='pending_cancellation';});
  var paymentBookings=unpaidOnly?(bookings||[]).filter(function(b){return b.status==='confirmed'&&b.payment_status!=='paid';}):(bookings||[]).filter(function(b){return b.status==='confirmed';});
  var handleSubmit=function(){if(!selUser||!selWeek)return;var u=users.find(function(x){return x.id===parseInt(selUser);});if(u)onBooked(selWeek,u,selPhase);onClose();};
  var tabBtn=function(id,label,badge){return h('button',{key:id,onClick:function(){setTab(id);},style:{flex:1,padding:'8px 0',border:'none',borderRadius:8,background:tab===id?COLORS.white:'transparent',color:tab===id?COLORS.text:COLORS.muted,fontWeight:tab===id?600:400,cursor:'pointer',fontSize:11,boxShadow:tab===id?'0 1px 4px rgba(0,0,0,0.08)':'none',fontFamily:"'DM Sans',sans-serif",position:'relative'}},label,badge>0&&h('span',{style:{position:'absolute',top:-4,right:4,background:COLORS.danger,color:'white',fontSize:9,fontWeight:700,padding:'1px 5px',borderRadius:8}},badge));};

  return h('div',{style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'},onClick:onClose},
    h('div',{onClick:function(e){e.stopPropagation();},style:{background:COLORS.white,borderRadius:16,padding:28,width:580,maxHeight:'85vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'}},
      h('h3',{style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:'0 0 6px 0',fontFamily:"'Playfair Display',Georgia,serif"}},t.adminTitle),
      h('p',{style:{fontSize:13,color:COLORS.muted,margin:'0 0 16px 0'}},t.adminDesc),
      h('div',{style:{display:'flex',gap:0,marginBottom:20,background:COLORS.bg,borderRadius:10,padding:3}},
        tabBtn('email','\u{1F4E7} '+t.emailTab,0),tabBtn('manual','\u270F\uFE0F '+t.manualTab,0),tabBtn('cancellations','\u274C '+t.cancellationsTab,pending.length),tabBtn('payments','\u{1F4B3} '+t.paymentsTab,0)),
      tab==='email'&&h('div',{style:{display:'flex',flexDirection:'column',gap:14}},
        h('textarea',{placeholder:t.pasteEmail,rows:6,style:{padding:'12px 16px',border:'1.5px solid '+COLORS.border,borderRadius:10,fontSize:14,outline:'none',resize:'vertical',fontFamily:'inherit',lineHeight:1.5}}),
        h('div',{style:{padding:16,background:'#3B6B9E10',border:'1px solid #3B6B9E30',borderRadius:10,textAlign:'center',fontSize:13,color:'#3B6B9E'}},'\u{1F916} Email parsing requires the backend API.'),
        h('button',{onClick:onClose,style:{padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.cancelBtn)),
      tab==='manual'&&h('div',{style:{display:'flex',flexDirection:'column',gap:14}},
        h('div',null,h('label',{style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6}},t.owner),
          h('select',{value:selUser,onChange:function(e){setSelUser(e.target.value);},style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans',sans-serif"}},h('option',{value:''},t.pickOwner),users.map(function(u){var br=FAMILY_BRANCHES.find(function(b){return b.id===u.branch_id;});return h('option',{key:u.id,value:u.id},u.name+' ('+(br?br.name:'?')+')');}))
        ),
        h('div',null,h('label',{style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6}},t.phase),
          h('div',{style:{display:'flex',gap:6}},PHASE_ORDER.map(function(pid){var pm=getPhaseMeta(pid,t);return h('button',{key:pid,onClick:function(){setSelPhase(pid);},style:{flex:1,padding:'8px 0',border:selPhase===pid?'1.5px solid #B85042':'1.5px solid '+COLORS.border,borderRadius:8,background:selPhase===pid?'#B850420A':COLORS.white,fontSize:11,fontWeight:600,color:selPhase===pid?'#B85042':COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},pm.icon+' '+pm.name);}))
        ),
        h('div',null,h('label',{style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6}},t.week),
          h('select',{value:selWeek,onChange:function(e){setSelWeek(e.target.value);},style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans',sans-serif"}},h('option',{value:''},t.pickWeek),weeks.map(function(w){return h('option',{key:w.id,value:w.id},'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang));}))
        ),
        h('div',{style:{display:'flex',gap:8,marginTop:4}},
          h('button',{onClick:onClose,style:{flex:1,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.cancelBtn),
          h('button',{onClick:handleSubmit,disabled:!selUser||!selWeek,style:{flex:1,padding:'11px 0',background:(!selUser||!selWeek)?'#D4C5B5':COLORS.success,border:'none',borderRadius:10,fontSize:14,fontWeight:600,color:'white',cursor:(!selUser||!selWeek)?'default':'pointer',fontFamily:"'DM Sans',sans-serif"}},t.bookBtn))),
      tab==='cancellations'&&h('div',{style:{display:'flex',flexDirection:'column',gap:10}},
        pending.length===0?h('div',{style:{padding:24,textAlign:'center',color:COLORS.muted,fontSize:14}},t.noPendingCancellations)
        :pending.map(function(b){var w=weeks.find(function(wk){return wk.id===b.week_id;});var br=FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});
          return h('div',{key:b.id,style:{padding:'14px 16px',background:'#FEF2F2',border:'1px solid #B8504230',borderRadius:10,display:'flex',alignItems:'center',gap:12}},
            h('div',{style:{flex:1}},h('div',{style:{fontSize:14,fontWeight:600,color:COLORS.text}},b.user_name),h('div',{style:{fontSize:12,color:COLORS.muted,marginTop:2}},(br?br.name:'')+' \u00B7 '+(w?'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang):b.week_id))),
            h('button',{onClick:function(){onApproveCancellation(b.id);},style:{padding:'6px 14px',background:COLORS.success,border:'none',borderRadius:8,fontSize:12,color:'white',cursor:'pointer',fontWeight:600,fontFamily:"'DM Sans',sans-serif"}},t.approveCancellation),
            h('button',{onClick:function(){onRejectCancellation(b.id);},style:{padding:'6px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans',sans-serif"}},t.rejectCancellation));}),
        h('button',{onClick:onClose,style:{marginTop:8,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.close)),
      tab==='payments'&&h('div',{style:{display:'flex',flexDirection:'column',gap:10}},
        h('div',{style:{display:'flex',gap:8,marginBottom:8}},
          h('button',{onClick:function(){setUnpaidOnly(false);},style:{padding:'6px 14px',borderRadius:8,border:'none',background:!unpaidOnly?COLORS.text:'#E8DFD5',color:!unpaidOnly?COLORS.white:COLORS.muted,fontSize:12,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.allPayments),
          h('button',{onClick:function(){setUnpaidOnly(true);},style:{padding:'6px 14px',borderRadius:8,border:'none',background:unpaidOnly?COLORS.text:'#E8DFD5',color:unpaidOnly?COLORS.white:COLORS.muted,fontSize:12,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.filterUnpaid)),
        paymentBookings.length===0?h('div',{style:{padding:24,textAlign:'center',color:COLORS.muted,fontSize:14}},t.noData)
        :paymentBookings.map(function(b){var w=weeks.find(function(wk){return wk.id===b.week_id;});var br=FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});var cost=calcBookingCost(b.guests,w?w.start:null);
          return h('div',{key:b.id,style:{padding:'12px 14px',background:'#F8F5F2',borderRadius:10,display:'flex',alignItems:'center',gap:10,borderLeft:'3px solid '+(br?br.color:'#ccc')}},
            h('div',{style:{flex:1}},h('div',{style:{fontSize:13,fontWeight:600,color:COLORS.text}},b.user_name),h('div',{style:{fontSize:11,color:COLORS.muted}},(w?'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang):b.week_id)),h('div',{style:{fontSize:12,fontWeight:600,color:COLORS.text,marginTop:4}},'\u20AC'+cost.total.toFixed(2))),
            paymentBadge(b.payment_status,t),
            h('select',{value:b.payment_status,onChange:function(e){onUpdatePayment(b.id,e.target.value);},style:{padding:'4px 8px',border:'1px solid '+COLORS.border,borderRadius:6,fontSize:11,background:'white',fontFamily:"'DM Sans',sans-serif"}},h('option',{value:'not_paid'},t.notPaid),h('option',{value:'invoice_sent'},t.invoiceSent),h('option',{value:'paid'},t.paid)));}),
        h('button',{onClick:onClose,style:{marginTop:8,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.close))));
}

// ═══════════════════════════════════════════════════════
// WEEK ROW
// ═══════════════════════════════════════════════════════
function WeekRow(p){
  var week=p.week,myBooking=p.myBooking,allBookings=p.allBookings,onBook=p.onBook,onRequestCancel=p.onRequestCancel,user=p.user,phase=p.phase,clanBookedByBranch=p.clanBookedByBranch,userBookedPriority=p.userBookedPriority,phaseActive=p.phaseActive,t=p.t,lang=p.lang;
  var hasMyBooking=!!myBooking;
  var userBranch=FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});
  var isPending=hasMyBooking&&myBooking.status==='pending_cancellation';
  var weekBookedByOther=false, weekNotOpen=false;
  if(phase==='regular'&&!hasMyBooking){
    var confirmed=(allBookings||[]).filter(function(b){return b.week_id===week.id&&b.status==='confirmed';});
    if(confirmed.length>0) weekBookedByOther=true;
    if(!isWeekBookableRegular(week.start)) weekNotOpen=true;
  }
  var bookable=false, reason='';
  if(!hasMyBooking&&phaseActive){
    if(phase==='clan'){if(clanBookedByBranch)reason=t.branchAlreadyBooked;else bookable=true;}
    else if(phase==='priority'){if(userBookedPriority)reason=t.youAlreadyBooked;else bookable=true;}
    else{if(weekBookedByOther)reason=t.weekBookedByOther;else if(weekNotOpen)reason=t.weekNotYetOpen+' '+formatFullDate(regularOpenDate(week.start),lang);else bookable=true;}
  }
  return h('div',{style:{display:'flex',alignItems:'center',padding:'10px 16px',borderRadius:10,background:isPending?'#FEF2F210':hasMyBooking?userBranch.color+'0D':'transparent',transition:'all 0.2s',gap:12,borderLeft:isPending?'3px solid '+COLORS.warning:hasMyBooking?'3px solid '+userBranch.color:'3px solid transparent',opacity:isPending?0.7:1}},
    h('div',{style:{width:36,textAlign:'center',fontSize:13,fontWeight:600,color:COLORS.muted}},'W'+week.weekNum),
    h('div',{style:{flex:1,minWidth:0}},
      h('div',{style:{fontSize:14,fontWeight:500,color:COLORS.text,textDecoration:isPending?'line-through':'none'}},formatDate(week.start,lang)+' \u2014 '+formatDate(week.end,lang)),
      hasMyBooking&&!isPending&&h('div',{style:{display:'flex',alignItems:'center',gap:6,marginTop:2}},h('span',{style:{fontSize:12,color:userBranch.color,fontWeight:600}},t.yourBooking+' \u00B7 '+getPhaseMeta(myBooking.phase,t).name+(myBooking.admin_booked?' \u00B7 '+t.adminBooking:'')),paymentBadge(myBooking.payment_status||'not_paid',t)),
      isPending&&h('div',{style:{fontSize:12,color:COLORS.warning,fontWeight:600,marginTop:2}},'\u23F3 '+t.pendingCancellation),
      !hasMyBooking&&reason&&h('div',{style:{fontSize:11,color:weekBookedByOther?COLORS.danger:'#B0A090',marginTop:2}},reason)),
    h('div',{style:{display:'flex',gap:6}},
      hasMyBooking&&!isPending&&h('button',{onClick:function(){onRequestCancel(myBooking);},style:{padding:'6px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans',sans-serif"}},t.requestCancel),
      isPending&&h('span',{style:{fontSize:11,color:COLORS.warning,fontWeight:600}},'\u23F3'),
      !hasMyBooking&&bookable&&h('button',{onClick:function(){onBook(week);},style:{padding:'6px 14px',background:COLORS.success,border:'none',borderRadius:8,fontSize:12,color:COLORS.white,cursor:'pointer',fontWeight:600,fontFamily:"'DM Sans',sans-serif"}},t.bookBtn)));
}

// ═══════════════════════════════════════════════════════
// BOOKING VIEW
// ═══════════════════════════════════════════════════════
function BookingView(p){
  var user=p.user,bookings=p.bookings,weeks=p.weeks,year=p.year,setYear=p.setYear,phase=p.phase,setPhase=p.setPhase,phaseConfig=p.phaseConfig,filterMonth=p.filterMonth,setFilterMonth=p.setFilterMonth,branches=p.branches,onSelectBranch=p.onSelectBranch,onShowAdmin=p.onShowAdmin,onBook=p.onBook,onRequestCancel=p.onRequestCancel,onSaveGuests=p.onSaveGuests,showToast=p.showToast,t=p.t,lang=p.lang;
  var _costBooking=useState(null),costBooking=_costBooking[0],setCostBooking=_costBooking[1];
  var myBookings=bookings.filter(function(b){return b.user_id===user.id;});
  var myBookingMap={};myBookings.forEach(function(b){myBookingMap[b.week_id]=b;});
  var clanBookedByBranch=bookings.find(function(b){return b.branch_id===user.branch_id&&b.phase==='clan'&&b.status==='confirmed';});
  var userBookedPriority=bookings.find(function(b){return b.user_id===user.id&&b.phase==='priority'&&b.status==='confirmed';});
  var filteredWeeks=filterMonth!==null?weeks.filter(function(w){return w.month===filterMonth;}):weeks;
  var userBranch=FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});

  return h('div',{style:{display:'flex',maxWidth:1200,margin:'0 auto',gap:24,padding:'24px 20px'}},
    costBooking&&h(CostCalculator,{booking:costBooking,weeks:weeks,onSave:function(id,guests){onSaveGuests(id,guests);setCostBooking(null);},onClose:function(){setCostBooking(null);},t:t,lang:lang}),
    // SIDEBAR
    h('div',{style:{width:300,flexShrink:0,display:'flex',flexDirection:'column',gap:16}},
      h('div',{style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
        h('h3',{style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'}},t.bookingPhase),
        h('div',{style:{display:'flex',flexDirection:'column',gap:6}},
          PHASE_ORDER.map(function(pid){
            var pm=getPhaseMeta(pid,t),status=getPhaseStatus(pid,phaseConfig),pc=phaseColor(pid),isCurrent=phase===pid,window=getPhaseWindow(pid,phaseConfig,lang);
            return h('button',{key:pid,onClick:function(){setPhase(pid);},style:{display:'flex',flexDirection:'column',gap:4,padding:'12px 14px',border:isCurrent?'1.5px solid '+pc:'1.5px solid transparent',borderRadius:10,background:isCurrent?pc+'0A':'#F8F5F2',cursor:'pointer',textAlign:'left',opacity:status==='upcoming'||status==='closed'?0.55:1}},
              h('div',{style:{display:'flex',alignItems:'center',gap:8}},
                h('span',{style:{fontSize:16}},pm.icon),
                h('span',{style:{fontSize:13,fontWeight:600,color:isCurrent?pc:COLORS.text}},pm.name),
                status==='active'&&h('span',{style:{fontSize:9,fontWeight:700,padding:'2px 7px',borderRadius:8,background:COLORS.success+'20',color:COLORS.success}},t.active),
                status==='closed'&&h('span',{style:{fontSize:9,fontWeight:700,padding:'2px 7px',borderRadius:8,background:'#DC262620',color:'#DC2626'}},t.closed),
                status==='upcoming'&&h('span',{style:{fontSize:9,fontWeight:700,padding:'2px 7px',borderRadius:8,background:COLORS.warning+'20',color:COLORS.warning}},t.opens)),
              h('div',{style:{fontSize:11,color:COLORS.muted,marginLeft:24}},pm.rule),
              h('div',{style:{fontSize:10,color:isCurrent?pc:COLORS.muted,marginLeft:24,fontWeight:500}},window));
          }))),
      h('div',{style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
        h('h3',{style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'}},t.year),
        h('div',{style:{display:'flex',gap:8}},[2026,2027].map(function(y){return h('button',{key:y,onClick:function(){setYear(y);},style:{flex:1,padding:'10px 0',border:year===y?'1.5px solid '+COLORS.success:'1.5px solid '+COLORS.border,borderRadius:8,background:year===y?COLORS.success+'0D':COLORS.white,color:year===y?COLORS.success:COLORS.muted,fontWeight:600,fontSize:14,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},y);}))),
      h('div',{style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
        h('h3',{style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'}},t.myBookings+' ('+myBookings.length+')'),
        myBookings.length===0?h('p',{style:{fontSize:13,color:'#B0A090',margin:0}},t.noBookingsYet)
        :h('div',{style:{display:'flex',flexDirection:'column',gap:6}},myBookings.map(function(b){var w=weeks.find(function(wk){return wk.id===b.week_id;});if(!w)return null;
          var isPending=b.status==='pending_cancellation';var cost=calcBookingCost(b.guests,w.start);
          return h('div',{key:b.id,style:{padding:'8px 12px',background:isPending?'#FEF2F2':'#F8F5F2',borderRadius:8,borderLeft:'3px solid '+(isPending?COLORS.warning:userBranch.color),opacity:isPending?0.7:1,cursor:isPending?'default':'pointer'},onClick:isPending?null:function(){setCostBooking(b);}},
            h('div',{style:{display:'flex',alignItems:'center',justifyContent:'space-between'}},h('div',{style:{fontSize:13,fontWeight:500,color:COLORS.text,textDecoration:isPending?'line-through':'none'}},formatDate(w.start,lang)+' \u2014 '+formatDate(w.end,lang)),paymentBadge(b.payment_status||'not_paid',t)),
            isPending?h('div',{style:{fontSize:11,color:COLORS.warning,marginTop:2,fontWeight:600}},'\u23F3 '+t.pendingCancellation)
            :h('div',{style:{fontSize:11,color:COLORS.muted,marginTop:2}},'W'+w.weekNum+' \u00B7 '+getPhaseMeta(b.phase,t).name+(cost.total>CLEANING_FEE?' \u00B7 \u20AC'+cost.total.toFixed(2):'')+(b.admin_booked?' \u00B7 '+t.adminBooking:'')));}))),
      h('div',{style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
        h('h3',{style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'}},t.familyBranches),
        h('div',{style:{display:'flex',flexDirection:'column',gap:4}},FAMILY_BRANCHES.map(function(b){var bd=branches.find(function(br){return br.id===b.id;});var mc=bd?bd.member_count:0;
          return h('button',{key:b.id,onClick:function(){onSelectBranch(b);},style:{display:'flex',alignItems:'center',gap:8,fontSize:13,padding:'8px 10px',borderRadius:8,border:'none',background:'transparent',cursor:'pointer',width:'100%',textAlign:'left',fontFamily:"'DM Sans',sans-serif"}},
            h('div',{style:{width:10,height:10,borderRadius:3,background:b.color,flexShrink:0}}),h('span',{style:{color:COLORS.text,flex:1}},b.name),h('span',{style:{fontSize:11,color:'#B0A090'}},mc+'\u{1F464}'));})),
        h('p',{style:{fontSize:11,color:'#B0A090',margin:'10px 0 0 0'}},t.clickBranch)),
      user.is_admin&&h('button',{onClick:onShowAdmin,style:{padding:'12px 16px',background:'#C4853B10',border:'1.5px solid #C4853B30',borderRadius:12,fontSize:13,fontWeight:600,color:'#C4853B',cursor:'pointer',display:'flex',alignItems:'center',gap:8,justifyContent:'center',fontFamily:"'DM Sans',sans-serif"}},'\u{1F511} '+t.admin)),
    // MAIN
    h('div',{style:{flex:1,minWidth:0}},
      h('div',{style:{display:'flex',gap:4,marginBottom:16,flexWrap:'wrap'}},
        h('button',{onClick:function(){setFilterMonth(null);},style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===null?COLORS.text:'#E8DFD5',color:filterMonth===null?COLORS.white:COLORS.muted,fontSize:12,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.all),
        MONTHS_SHORT[lang].map(function(m,i){if(!weeks.some(function(w){return w.month===i;}))return null;
          return h('button',{key:m,onClick:function(){setFilterMonth(i);},style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===i?COLORS.text:'#E8DFD5',color:filterMonth===i?COLORS.white:COLORS.muted,fontSize:12,fontWeight:500,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},m);})),
      h('div',{style:{background:COLORS.white,borderRadius:14,padding:8,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
        filteredWeeks.length===0?h('div',{style:{padding:40,textAlign:'center',color:COLORS.muted}},t.noWeeks)
        :filteredWeeks.reduce(function(acc,week,i){
          var prevMonth=i>0?filteredWeeks[i-1].month:-1;
          if(week.month!==prevMonth) acc.push(h('div',{key:'month-'+week.month,style:{padding:'14px 16px 6px 16px',fontSize:13,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',borderTop:i>0?'1px solid #F0EBE5':'none',marginTop:i>0?4:0}},MONTHS_SHORT[lang][week.month]+' '+year));
          acc.push(h(WeekRow,{key:week.id,week:week,myBooking:myBookingMap[week.id],allBookings:bookings,onBook:function(w){onBook(w,phase);},onRequestCancel:onRequestCancel,user:user,phase:phase,clanBookedByBranch:clanBookedByBranch,userBookedPriority:userBookedPriority,phaseActive:getPhaseStatus(phase,phaseConfig)==='active'||user.is_admin,t:t,lang:lang}));
          return acc;
        },[]))));
}

// ═══════════════════════════════════════════════════════
// CALENDAR VIEW
// ═══════════════════════════════════════════════════════
function CalendarView(p){
  var user=p.user,bookings=p.bookings,weeks=p.weeks,year=p.year,phaseConfig=p.phaseConfig,t=p.t,lang=p.lang;
  var _cm=useState(function(){return new Date().getMonth();}),calMonth=_cm[0],setCalMonth=_cm[1];
  var _cy=useState(function(){return year;}),calYear=_cy[0],setCalYear=_cy[1];
  var _popup=useState(null),popup=_popup[0],setPopup=_popup[1];
  var today=toISO(new Date());
  var clanRevealed=phaseConfig?today>=phaseConfig.clan_reveal:false;
  var priorityRevealed=phaseConfig?today>=phaseConfig.priority_reveal:false;
  var visibleBookings=bookings.filter(function(b){if(b.phase==='regular')return true;if(b.user_id===user.id)return true;if(user.is_admin)return true;if(b.phase==='clan')return clanRevealed;if(b.phase==='priority')return priorityRevealed;return false;});
  var someHidden=!clanRevealed||!priorityRevealed;
  var goNext=function(){if(calMonth===11){setCalMonth(0);setCalYear(function(y){return y+1;});}else setCalMonth(function(m){return m+1;});};
  var goPrev=function(){if(calMonth===0){setCalMonth(11);setCalYear(function(y){return y-1;});}else setCalMonth(function(m){return m-1;});};
  var firstOfMonth=new Date(calYear,calMonth,1);
  var startDow=(firstOfMonth.getDay()+6)%7;
  var daysInMonth=new Date(calYear,calMonth+1,0).getDate();
  var cells=[];
  for(var i=0;i<startDow;i++) cells.push(null);
  for(var d=1;d<=daysInMonth;d++) cells.push(d);
  while(cells.length%7!==0) cells.push(null);
  var dayBookings={};
  for(var dd=1;dd<=daysInMonth;dd++){
    var ds=calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(dd).padStart(2,'0');
    dayBookings[dd]=visibleBookings.filter(function(b){var w=weeks.find(function(wk){return wk.id===b.week_id;});return w&&ds>=w.start&&ds<=w.end;});
  }
  var rows=[];for(var ri=0;ri<cells.length;ri+=7) rows.push(cells.slice(ri,ri+7));
  var dayNames=[t.mon,t.tue,t.wed,t.thu,t.fri,t.sat,t.sun];
  var navBtnStyle={padding:'8px 16px',border:'1.5px solid '+COLORS.border,borderRadius:8,background:COLORS.white,cursor:'pointer',fontSize:16,color:COLORS.text,fontFamily:"'DM Sans',sans-serif"};

  return h('div',{style:{maxWidth:960,margin:'0 auto',padding:'24px 20px'}},
    h('div',{style:{display:'flex',alignItems:'center',justifyContent:'center',gap:16,marginBottom:20}},
      h('button',{onClick:goPrev,style:navBtnStyle},'\u25C0'),
      h('h2',{style:{fontSize:20,fontWeight:700,color:COLORS.text,fontFamily:"'Playfair Display',Georgia,serif",margin:0,minWidth:220,textAlign:'center'}},FULL_MONTHS[lang][calMonth]+' '+calYear),
      h('button',{onClick:goNext,style:navBtnStyle},'\u25B6')),
    someHidden&&h('div',{style:{background:'#C4853B10',border:'1px solid #C4853B30',borderRadius:12,padding:'12px 18px',fontSize:13,color:'#8B6030',display:'flex',alignItems:'center',gap:8,marginBottom:16}},'\u{1F512} '+t.hiddenCalNote),
    h('div',{style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
      h('div',{style:{display:'grid',gridTemplateColumns:'repeat(7,1fr)',gap:2,marginBottom:4}},
        dayNames.map(function(name){return h('div',{key:name,style:{textAlign:'center',fontSize:11,fontWeight:700,color:COLORS.muted,padding:'8px 0',textTransform:'uppercase',letterSpacing:'0.5px'}},name);})),
      rows.map(function(row,rowIdx){
        return h('div',{key:rowIdx,style:{display:'grid',gridTemplateColumns:'repeat(7,1fr)',gap:2}},
          row.map(function(day,ci){
            if(!day) return h('div',{key:'e'+ci,style:{minHeight:64}});
            var bks=dayBookings[day]||[];
            var isToday=(calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(day).padStart(2,'0'))===today;
            return h('div',{key:day,style:{minHeight:64,padding:'3px 2px',position:'relative',border:isToday?'2px solid '+COLORS.accent:'2px solid transparent',borderRadius:6}},
              h('div',{style:{fontSize:12,fontWeight:isToday?700:400,color:COLORS.text,textAlign:'center',marginBottom:2}},day),
              bks.slice(0,3).map(function(b,bi){
                var br=FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});
                var w=weeks.find(function(wk){return wk.id===b.week_id;});
                var isPending=b.status==='pending_cancellation';
                var prevDay=ci>0?row[ci-1]:null;var nextDay=ci<6?row[ci+1]:null;
                var prevHas=prevDay&&(dayBookings[prevDay]||[]).some(function(x){return x.id===b.id;});
                var nextHas=nextDay&&(dayBookings[nextDay]||[]).some(function(x){return x.id===b.id;});
                var radius=(!prevHas?'4px ':'0 ')+(!nextHas?'4px ':'0 ')+(!nextHas?'4px ':'0 ')+(!prevHas?'4px':'0');
                return h('div',{key:b.id+'_'+bi,onClick:function(e){e.stopPropagation();setPopup(b);},style:{height:16,marginBottom:1,background:isPending?(br?br.color:'#ccc')+'40':(br?br.color:'#ccc')+'CC',borderRadius:radius,cursor:'pointer',display:'flex',alignItems:'center',overflow:'hidden',paddingLeft:!prevHas?3:0}},
                  !prevHas&&h('span',{style:{fontSize:8,fontWeight:700,color:'white',whiteSpace:'nowrap',textOverflow:'ellipsis',overflow:'hidden',textDecoration:isPending?'line-through':'none'}},shortName(b.user_name)));
              }));
          }));
      })),
    popup&&h('div',{style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.2)',zIndex:300,display:'flex',alignItems:'center',justifyContent:'center'},onClick:function(){setPopup(null);}},
      h('div',{onClick:function(e){e.stopPropagation();},style:{background:COLORS.white,borderRadius:14,padding:24,minWidth:280,maxWidth:400,boxShadow:'0 8px 32px rgba(0,0,0,0.15)'}},
        (function(){var b=popup;var br=FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});var w=weeks.find(function(wk){return wk.id===b.week_id;});var isPending=b.status==='pending_cancellation';
          return h(Fragment,null,
            h('div',{style:{display:'flex',alignItems:'center',gap:8,marginBottom:12}},h('div',{style:{width:12,height:12,borderRadius:3,background:br?br.color:'#ccc'}}),h('h4',{style:{fontSize:16,fontWeight:700,color:COLORS.text,margin:0,textDecoration:isPending?'line-through':'none'}},b.user_name)),
            h('div',{style:{display:'flex',flexDirection:'column',gap:6,fontSize:13,color:COLORS.text}},
              h('div',null,h('strong',null,t.branchLabel+': '),br?br.name:'?'),
              h('div',null,h('strong',null,t.phase+': '),getPhaseName(b.phase,t)),
              w&&h('div',null,h('strong',null,t.week+': '),'W'+w.weekNum+' \u2013 '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang)),
              b.created_at&&h('div',null,h('strong',null,t.booked+': '),formatFullDate(b.created_at,lang)),
              isPending&&h('div',{style:{color:COLORS.warning,fontWeight:600}},'\u23F3 '+t.pendingCancellation),
              b.admin_booked&&h('div',{style:{color:COLORS.warning,fontSize:12}},t.adminBooking)),
            h('button',{onClick:function(){setPopup(null);},style:{marginTop:16,width:'100%',padding:'10px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},t.close));
        })())));
}

// ═══════════════════════════════════════════════════════
// STATISTICS VIEW
// ═══════════════════════════════════════════════════════
function StatisticsView(p){
  var user=p.user,bookings=p.bookings,weeks=p.weeks,year=p.year,userStats=p.userStats,t=p.t,lang=p.lang;
  if(!user.is_admin) return h('div',{style:{maxWidth:600,margin:'0 auto',padding:'60px 20px',textAlign:'center'}},h('div',{style:{fontSize:48,marginBottom:16}},'\u{1F512}'),h('div',{style:{fontSize:16,color:COLORS.muted}},t.adminOnly));
  var confirmed=bookings.filter(function(b){return b.status==='confirmed';});
  var perUser={};confirmed.forEach(function(b){if(!perUser[b.user_name])perUser[b.user_name]={clan:0,priority:0,regular:0,total:0,branch_id:b.branch_id};perUser[b.user_name][b.phase]++;perUser[b.user_name].total++;});
  var perBranch={};FAMILY_BRANCHES.forEach(function(br){perBranch[br.name]=0;});confirmed.forEach(function(b){var br=FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});if(br)perBranch[br.name]++;});
  var maxBranch=Math.max.apply(null,Object.values(perBranch).concat([1]));
  var totalWeeks=weeks.length;var bookedIds={};confirmed.forEach(function(b){bookedIds[b.week_id]=true;});var bookedCount=Object.keys(bookedIds).length;var pct=totalWeeks>0?Math.round(bookedCount/totalWeeks*100):0;
  var timeline={};MONTHS_SHORT[lang].forEach(function(m,i){timeline[i]=0;});confirmed.forEach(function(b){if(b.created_at){var m=parseInt(b.created_at.split('-')[1])-1;timeline[m]++;}});var maxTL=Math.max.apply(null,Object.values(timeline).concat([1]));
  var card={background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)',marginBottom:16};
  var head={fontSize:13,fontWeight:700,color:COLORS.text,margin:'0 0 14px 0',fontFamily:"'Playfair Display',Georgia,serif"};
  var th={fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',padding:'8px 10px',textAlign:'left',borderBottom:'1px solid '+COLORS.border};
  var td={fontSize:13,padding:'8px 10px',borderBottom:'1px solid #F0EBE5',color:COLORS.text};

  return h('div',{style:{maxWidth:900,margin:'0 auto',padding:'24px 20px'}},
    h('h2',{style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 20px 0',fontFamily:"'Playfair Display',Georgia,serif"}},'\u{1F4CA} '+t.statsTitle+' '+year),
    h('div',{style:card},h('h3',{style:head},t.overallOccupancy),
      h('div',{style:{display:'flex',gap:20,alignItems:'center',flexWrap:'wrap'}},
        h('div',{style:{width:100,height:100,borderRadius:'50%',background:'conic-gradient('+COLORS.success+' '+pct+'%, #E8DFD5 0)',display:'flex',alignItems:'center',justifyContent:'center'}},
          h('div',{style:{width:72,height:72,borderRadius:'50%',background:COLORS.white,display:'flex',alignItems:'center',justifyContent:'center',fontSize:20,fontWeight:700,color:COLORS.success}},pct+'%')),
        h('div',{style:{display:'flex',flexDirection:'column',gap:4}},h('div',{style:{fontSize:13}},h('strong',null,bookedCount),' '+t.bookedWeeks),h('div',{style:{fontSize:13}},h('strong',null,totalWeeks-bookedCount),' '+t.availableWeeks),h('div',{style:{fontSize:13,color:COLORS.muted}},t.totalWeeks+': '+totalWeeks)))),
    h('div',{style:card},h('h3',{style:head},t.bookingsPerUser),
      h('table',{style:{width:'100%',borderCollapse:'collapse'}},
        h('thead',null,h('tr',null,h('th',{style:th},t.userName),h('th',{style:th},t.branchLabel),h('th',{style:Object.assign({},th,{textAlign:'center'})},t.clanName),h('th',{style:Object.assign({},th,{textAlign:'center'})},t.priorityName),h('th',{style:Object.assign({},th,{textAlign:'center'})},t.regularName),h('th',{style:Object.assign({},th,{textAlign:'center'})},t.total),h('th',{style:th},t.lastLogin))),
        h('tbody',null,Object.entries(perUser).map(function(e){var name=e[0],data=e[1];var br=FAMILY_BRANCHES.find(function(x){return x.id===data.branch_id;});var st=userStats[name]||{};
          return h('tr',{key:name},h('td',{style:td},name),h('td',{style:td},h('span',{style:{display:'inline-flex',alignItems:'center',gap:4}},h('div',{style:{width:8,height:8,borderRadius:2,background:br?br.color:'#ccc'}}),br?br.name:'?')),h('td',{style:Object.assign({},td,{textAlign:'center'})},data.clan||'-'),h('td',{style:Object.assign({},td,{textAlign:'center'})},data.priority||'-'),h('td',{style:Object.assign({},td,{textAlign:'center'})},data.regular||'-'),h('td',{style:Object.assign({},td,{textAlign:'center',fontWeight:700})},data.total),h('td',{style:Object.assign({},td,{fontSize:11,color:COLORS.muted})},st.lastLogin||'-'));}))),
      Object.keys(perUser).length===0&&h('div',{style:{padding:16,textAlign:'center',color:COLORS.muted,fontSize:13}},t.noData)),
    h('div',{style:card},h('h3',{style:head},t.bookingsPerBranch),
      h('div',{style:{display:'flex',flexDirection:'column',gap:8}},FAMILY_BRANCHES.map(function(br){var count=perBranch[br.name]||0;var w=maxBranch>0?Math.round(count/maxBranch*100):0;
        return h('div',{key:br.id,style:{display:'flex',alignItems:'center',gap:10}},h('div',{style:{width:120,fontSize:12,fontWeight:500,color:COLORS.text,textAlign:'right'}},br.name),h('div',{style:{flex:1,background:'#F0EBE5',borderRadius:6,height:24,overflow:'hidden'}},h('div',{style:{width:w+'%',height:'100%',background:br.color,borderRadius:6,transition:'width 0.3s',minWidth:count>0?20:0}})),h('div',{style:{width:30,fontSize:13,fontWeight:600,color:br.color,textAlign:'center'}},count));}))),
    h('div',{style:card},h('h3',{style:head},t.bookingTimeline),
      h('div',{style:{display:'flex',alignItems:'flex-end',gap:4,height:120,padding:'0 4px'}},MONTHS_SHORT[lang].map(function(m,i){var count=timeline[i]||0;var pctH=maxTL>0?Math.round(count/maxTL*100):0;
        return h('div',{key:m,style:{flex:1,display:'flex',flexDirection:'column',alignItems:'center',gap:4}},h('div',{style:{fontSize:10,fontWeight:600,color:COLORS.text}},count>0?count:''),h('div',{style:{width:'100%',background:count>0?COLORS.success:'#E8DFD5',borderRadius:'4px 4px 0 0',height:Math.max(pctH,4)+'%',transition:'height 0.3s',minHeight:4}}),h('div',{style:{fontSize:9,color:COLORS.muted,marginTop:4}},m));}))));
}

// ═══════════════════════════════════════════════════════
// FEEDBACK VIEW (Google Form embed)
// ═══════════════════════════════════════════════════════
function FeedbackView(p){
  return h('div',{style:{maxWidth:900,margin:'0 auto',padding:'24px 20px'}},
    h('div',{style:{background:COLORS.white,borderRadius:16,overflow:'hidden',boxShadow:'0 1px 8px rgba(0,0,0,0.04)'}},
      h('iframe',{src:'https://docs.google.com/forms/d/e/1FAIpQLScdbJ70TdJNOawH0VjDpkvk0QgDDUhI3AXdMApr54YGAmKLzg/viewform?embedded=true',style:{width:'100%',minHeight:'80vh',border:'none'}, title:'Feedback Form'})));
}

// ═══════════════════════════════════════════════════════
// WHATSAPP BUTTON
// ═══════════════════════════════════════════════════════
function WhatsAppButton(p){
  var t=p.t;
  var _hover=useState(false),hover=_hover[0],setHover=_hover[1];
  var svgIcon=h('svg',{viewBox:'0 0 24 24',width:28,height:28,fill:'white'},h('path',{d:'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z'}));
  return h('a',{href:'https://wa.me/4915788611151',target:'_blank',rel:'noopener noreferrer',
    onMouseEnter:function(){setHover(true);},onMouseLeave:function(){setHover(false);},
    style:{position:'fixed',bottom:20,right:20,width:56,height:56,borderRadius:'50%',background:'#25D366',display:'flex',alignItems:'center',justifyContent:'center',boxShadow:'0 4px 16px rgba(0,0,0,0.2)',cursor:'pointer',zIndex:150,textDecoration:'none',transition:'transform 0.2s',transform:hover?'scale(1.1)':'scale(1)'}},
    svgIcon,
    hover&&h('div',{style:{position:'absolute',bottom:64,right:0,background:COLORS.text,color:COLORS.white,padding:'6px 12px',borderRadius:8,fontSize:12,fontWeight:500,whiteSpace:'nowrap',boxShadow:'0 2px 8px rgba(0,0,0,0.15)'}},t.whatsappHelp));
}

// ═══════════════════════════════════════════════════════
// LOGIN SCREEN
// ═══════════════════════════════════════════════════════
function LoginScreen(p){
  var t=p.t,lang=p.lang,onLangToggle=p.onLangToggle,onLogin=p.onLogin;
  var _mode=useState('login'),mode=_mode[0],setMode=_mode[1];
  var _email=useState(''),email=_email[0],setEmail=_email[1];
  var _name=useState(''),name=_name[0],setName=_name[1];
  var _pw=useState(''),pw=_pw[0],setPw=_pw[1];
  var _br=useState(''),branchId=_br[0],setBranchId=_br[1];
  var _err=useState(''),error=_err[0],setError=_err[1];
  var handleSubmit=function(e){e.preventDefault();setError('');if(!email.trim()||!pw.trim())return setError(t.fillAll);if(!email.includes('@'))return setError(t.invalidEmail);if(mode==='register'&&!name.trim())return setError(t.fillAll);if(mode==='register'&&!branchId)return setError(t.pickBranch);var isAdmin=email.toLowerCase().includes('marielle');var bid=mode==='register'?parseInt(branchId):1;var displayName=mode==='register'?name.trim():email.split('@')[0];onLogin({id:Date.now(),name:displayName,email:email.trim(),branch_id:bid,is_admin:isAdmin});};
  var iS={width:'100%',padding:'12px 16px',border:'1px solid '+COLORS.border,borderRadius:8,fontSize:14,fontFamily:"'DM Sans',sans-serif",color:COLORS.text,outline:'none',marginBottom:12,boxSizing:'border-box'};
  return h('div',{style:{minHeight:'100vh',display:'flex',alignItems:'center',justifyContent:'center',background:COLORS.bg,fontFamily:"'DM Sans',sans-serif",padding:20}},
    h('div',{style:{background:COLORS.white,borderRadius:16,padding:'48px 40px',maxWidth:420,width:'100%',boxShadow:'0 4px 24px rgba(44,24,16,0.08)',position:'relative'}},
      h('button',{onClick:onLangToggle,style:{position:'absolute',top:16,right:16,background:'none',border:'1px solid '+COLORS.border,borderRadius:8,padding:'4px 12px',cursor:'pointer',fontSize:13,color:COLORS.muted,fontFamily:"'DM Sans',sans-serif"}},lang==='en'?'NL':'EN'),
      h('h1',{style:{fontFamily:"'Playfair Display',serif",fontSize:32,fontWeight:700,color:COLORS.text,margin:'0 0 4px 0',textAlign:'center'}},t.appName),
      h('p',{style:{fontSize:14,color:COLORS.muted,margin:'0 0 32px 0',textAlign:'center'}},t.subtitle),
      h('div',{style:{display:'flex',gap:0,marginBottom:24,borderBottom:'2px solid '+COLORS.border}},['login','register'].map(function(m){return h('button',{key:m,onClick:function(){setMode(m);setError('');},style:{flex:1,padding:'10px 16px',background:'none',border:'none',borderBottom:'2px solid '+(mode===m?COLORS.accent:'transparent'),marginBottom:-2,cursor:'pointer',fontSize:14,fontWeight:600,color:mode===m?COLORS.text:COLORS.muted,fontFamily:"'DM Sans',sans-serif"}},m==='login'?t.signIn:t.register);})),
      h('form',{onSubmit:handleSubmit},
        error&&h('div',{style:{background:'#FEF2F2',color:COLORS.danger,padding:'10px 14px',borderRadius:8,fontSize:13,marginBottom:12}},error),
        h('input',{style:iS,type:'email',placeholder:t.email,value:email,onChange:function(e){setEmail(e.target.value);}}),
        mode==='register'&&h('input',{style:iS,type:'text',placeholder:t.displayName,value:name,onChange:function(e){setName(e.target.value);}}),
        h('input',{style:iS,type:'password',placeholder:t.password,value:pw,onChange:function(e){setPw(e.target.value);}}),
        mode==='register'&&h(Fragment,null,
          h('label',{style:{display:'block',fontSize:13,fontWeight:600,color:COLORS.muted,marginBottom:6}},t.whichBranch),
          h('select',{style:Object.assign({},iS,{cursor:'pointer',background:COLORS.white}),value:branchId,onChange:function(e){setBranchId(e.target.value);}},h('option',{value:''},t.pickBranch),FAMILY_BRANCHES.map(function(b){return h('option',{key:b.id,value:b.id},b.name);}))),
        h('button',{type:'submit',style:{width:'100%',padding:12,background:COLORS.accent,color:COLORS.white,border:'none',borderRadius:8,fontSize:15,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans',sans-serif",marginTop:8}},mode==='login'?t.signIn:t.createAccount),
        h('p',{style:{fontSize:11,color:'#B0A090',textAlign:'center',marginTop:16}},'Demo: Enter any email/password. Include "marielle" in email for admin access.'))));
}

// ═══════════════════════════════════════════════════════
// MAIN APP
// ═══════════════════════════════════════════════════════
function App(){
  var _lang=useState(localStorage.getItem('fargny_lang')||'nl'),lang=_lang[0],setLang=_lang[1];
  var _user=useState(null),user=_user[0],setUser=_user[1];
  var _view=useState('book'),view=_view[0],setView=_view[1];
  var _year=useState(2026),year=_year[0],setYear=_year[1];
  var _phase=useState('regular'),phase=_phase[0],setPhase=_phase[1];
  var _bookings=useState(INITIAL_BOOKINGS),bookings=_bookings[0],setBookings=_bookings[1];
  var _fm=useState(null),filterMonth=_fm[0],setFilterMonth=_fm[1];
  var _toast=useState(null),toast=_toast[0],setToast=_toast[1];
  var _sb=useState(null),selectedBranch=_sb[0],setSelectedBranch=_sb[1];
  var _sap=useState(false),showAdmin=_sap[0],setShowAdmin=_sap[1];
  var _us=useState({}),userStats=_us[0],setUserStats=_us[1];
  var t=T[lang]||T.en;
  var weeks=generateWeeks(year);
  var phaseConfig=MOCK_PHASE_CONFIG;
  var toggleLang=function(){var next=lang==='en'?'nl':'en';setLang(next);localStorage.setItem('fargny_lang',next);};
  var showToast=function(msg){setToast(msg);setTimeout(function(){setToast(null);},3500);};
  var handleLogin=function(userData){setUser(userData);setUserStats(function(prev){var u=Object.assign({},prev);u[userData.name]=Object.assign({},u[userData.name]||{},{lastLogin:new Date().toLocaleString(),bookingCount:(u[userData.name]||{}).bookingCount||0});return u;});};
  var handleBook=function(week,bookingPhase){
    var br=FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});
    setBookings(function(prev){return prev.concat([{id:Date.now(),week_id:week.id,year:year,user_id:user.id,branch_id:user.branch_id,phase:bookingPhase,admin_booked:false,user_name:user.name,user_email:user.email,branch_name:br?br.name:'',status:'confirmed',created_at:toISO(new Date()),payment_status:'not_paid',guests:null}]);});
    setUserStats(function(prev){var u=Object.assign({},prev);var s=u[user.name]||{lastLogin:new Date().toLocaleString(),bookingCount:0};s.bookingCount++;u[user.name]=s;return u;});
    showToast(t.booked+': '+formatDate(week.start,lang)+' \u2014 '+formatDate(week.end,lang)+'\n'+t.confirmationEmailSent+' '+(user.email||''));
  };
  var handleRequestCancel=function(booking){setBookings(function(prev){return prev.map(function(b){return b.id===booking.id?Object.assign({},b,{status:'pending_cancellation'}):b;});});showToast(t.cancelRequested);};
  var handleApproveCancellation=function(id){setBookings(function(prev){return prev.filter(function(b){return b.id!==id;});});showToast(t.cancellationApproved);};
  var handleRejectCancellation=function(id){setBookings(function(prev){return prev.map(function(b){return b.id===id?Object.assign({},b,{status:'confirmed'}):b;});});showToast(t.cancellationRejected);};
  var handleUpdatePayment=function(id,status){setBookings(function(prev){return prev.map(function(b){return b.id===id?Object.assign({},b,{payment_status:status}):b;});});};
  var handleSaveGuests=function(id,guests){setBookings(function(prev){return prev.map(function(b){return b.id===id?Object.assign({},b,{guests:guests}):b;});});showToast('\u2705 '+t.saveCosts);};
  var handleAdminBook=function(weekId,targetUser,bookingPhase){
    var w=weeks.find(function(wk){return wk.id===weekId;});var br=FAMILY_BRANCHES.find(function(b){return b.id===targetUser.branch_id;});
    setBookings(function(prev){return prev.concat([{id:Date.now(),week_id:weekId,year:year,user_id:targetUser.id,branch_id:targetUser.branch_id,phase:bookingPhase,admin_booked:true,user_name:targetUser.name,user_email:targetUser.email,branch_name:br?br.name:'',status:'confirmed',created_at:toISO(new Date()),payment_status:'not_paid',guests:null}]);});
    showToast(t.booked+': '+targetUser.name+' - '+(w?formatDate(w.start,lang):weekId));
  };
  if(!user) return h(LoginScreen,{t:t,lang:lang,onLangToggle:toggleLang,onLogin:handleLogin});
  var userBranch=FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;})||FAMILY_BRANCHES[0];
  var yearBookings=bookings.filter(function(b){return b.year===year;});
  var totalBooked=yearBookings.filter(function(b){return b.status==='confirmed';}).length;
  var pendingCount=yearBookings.filter(function(b){return b.status==='pending_cancellation';}).length;
  var navItems=[{id:'book',label:t.book,emoji:'\u{1F4DD}'},{id:'calendar',label:t.calendar,emoji:'\u{1F4C5}'},{id:'feedback',label:t.feedback,emoji:'\u{1F4CB}'}];
  if(user.is_admin) navItems.push({id:'statistics',label:t.statistics,emoji:'\u{1F4CA}'});

  return h('div',{style:{minHeight:'100vh',background:COLORS.bg,fontFamily:"'DM Sans','Segoe UI',sans-serif"}},
    selectedBranch&&h(BranchMembersPanel,{branch:selectedBranch,onClose:function(){setSelectedBranch(null);},t:t}),
    showAdmin&&h(AdminPanel,{users:MOCK_USERS,weeks:weeks,year:year,bookings:yearBookings,onBooked:handleAdminBook,onApproveCancellation:handleApproveCancellation,onRejectCancellation:handleRejectCancellation,onUpdatePayment:handleUpdatePayment,onClose:function(){setShowAdmin(false);},t:t,lang:lang}),
    h(Toast,{message:toast}),
    h('div',{style:{background:COLORS.white,borderBottom:'1px solid '+COLORS.border,padding:'12px 20px',display:'flex',alignItems:'center',justifyContent:'space-between',position:'sticky',top:0,zIndex:100,flexWrap:'wrap',gap:8}},
      h('div',{style:{display:'flex',alignItems:'center',gap:10}},h('span',{style:{fontSize:22}},'\u{1F3E1}'),h('div',null,h('h1',{style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display',Georgia,serif"}},'Fargny'),h('p',{style:{fontSize:11,color:COLORS.muted,margin:0}},year+' \u00B7 '+totalBooked+' '+t.weeksBooked+(pendingCount>0?' \u00B7 '+pendingCount+' pending':'')))),
      h('div',{style:{display:'flex',gap:0,background:COLORS.bg,borderRadius:10,padding:3}},navItems.map(function(v){return h('button',{key:v.id,onClick:function(){setView(v.id);},style:{padding:'7px 16px',border:'none',borderRadius:8,background:view===v.id?COLORS.white:'transparent',color:view===v.id?COLORS.text:COLORS.muted,fontWeight:view===v.id?600:400,cursor:'pointer',fontSize:13,fontFamily:"'DM Sans',sans-serif",boxShadow:view===v.id?'0 1px 4px rgba(0,0,0,0.08)':'none'}},v.emoji+' '+v.label);})),
      h('div',{style:{display:'flex',alignItems:'center',gap:10}},
        h('button',{onClick:toggleLang,style:{padding:'6px 12px',border:'1.5px solid '+COLORS.border,borderRadius:8,background:COLORS.white,fontSize:12,fontWeight:600,color:COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans',sans-serif"}},lang==='en'?'\u{1F1F3}\u{1F1F1} NL':'\u{1F1EC}\u{1F1E7} EN'),
        h('div',{style:{textAlign:'right'}},h('div',{style:{fontSize:13,fontWeight:600,color:COLORS.text}},user.name),h('div',{style:{fontSize:11,color:userBranch.color,fontWeight:600}},userBranch.name)),
        h('button',{onClick:function(){setUser(null);},style:{padding:'7px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans',sans-serif"}},t.signOut))),
    view==='book'&&h(BookingView,{user:user,bookings:yearBookings,weeks:weeks,year:year,setYear:function(y){setYear(y);setFilterMonth(null);},phase:phase,setPhase:setPhase,phaseConfig:phaseConfig,filterMonth:filterMonth,setFilterMonth:setFilterMonth,branches:MOCK_BRANCHES,onSelectBranch:setSelectedBranch,onShowAdmin:function(){setShowAdmin(true);},onBook:handleBook,onRequestCancel:handleRequestCancel,onSaveGuests:handleSaveGuests,showToast:showToast,t:t,lang:lang}),
    view==='calendar'&&h(CalendarView,{user:user,bookings:yearBookings,weeks:weeks,year:year,phaseConfig:phaseConfig,branches:MOCK_BRANCHES,onSelectBranch:setSelectedBranch,t:t,lang:lang}),
    view==='feedback'&&h(FeedbackView,{t:t,lang:lang}),
    view==='statistics'&&h(StatisticsView,{user:user,bookings:yearBookings,weeks:weeks,year:year,userStats:userStats,t:t,lang:lang}),
    h(WhatsAppButton,{t:t}));
}

var root=ReactDOM.createRoot(document.getElementById('fargny-booking-root'));
root.render(h(App));
