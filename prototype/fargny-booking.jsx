const { useState, useEffect, useCallback } = React;
const h = React.createElement;

// ═══════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════
const FAMILY_BRANCHES = [
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

const PHASES = {
  clan: { id: "clan", icon: "\u{1F3E0}", revealable: true },
  priority: { id: "priority", icon: "\u2B50", revealable: true },
  regular: { id: "regular", icon: "\u{1F33F}", revealable: false },
};

const COLORS = {
  bg: '#F5F0EB', text: '#2C1810', muted: '#8B7D6B', border: '#E0D8CF',
  white: '#FFFFFF', accent: '#B85042', success: '#4A7C59', warning: '#C4853B', danger: '#B85042',
};

const PHASE_ORDER = ['clan', 'priority', 'regular'];

// ═══════════════════════════════════════════════════════
// TRANSLATIONS
// ═══════════════════════════════════════════════════════
const T = {
  en: {
    appName:"Fargny",subtitle:"Holiday House Booking",signIn:"Sign In",register:"Register",email:"Email address",displayName:"Display name",password:"Password",fillAll:"Please fill in all fields",invalidEmail:"Please enter a valid email",pickBranch:"Please select your family branch",whichBranch:"Which family branch do you belong to?",createAccount:"Create Account",nameTaken:"Email already registered",invalidLogin:"Invalid email or password",signOut:"Sign Out",weeksBooked:"weeks booked",book:"Book",calendar:"Calendar",statistics:"Statistics",bookingPhase:"Booking Phase",year:"Year",myBookings:"My Bookings",noBookingsYet:"No bookings yet",familyBranches:"Family Branches",clickBranch:"Click a branch to see its members",all:"All",noWeeks:"No weeks available",bookBtn:"Book",cancelBtn:"Cancel",yourBooking:"Your booking",branchAlreadyBooked:"Your branch already booked a week",youAlreadyBooked:"You already booked a week",alreadyBookedWeek:"You already booked this week!",branchBookedPhase:"Your branch already booked a week this phase",youBookedPhase:"You already booked a week this phase",booked:"Booked",bookingCancelled:"Booking cancelled",available:"Available",doubleBooking:"Double booking",noMembers:"No members registered yet",membersRegistered:"members registered",memberRegistered:"member registered",close:"Close",admin:"Admin",adminTitle:"Book on behalf of someone",adminDesc:"For owners who book via email",owner:"Owner",pickOwner:"Choose an owner...",phase:"Phase",week:"Week",pickWeek:"Choose a week...",clanName:"Clan Booking",clanDesc:"Each family branch picks one week",clanRule:"One week per family branch",priorityName:"Priority Booking",priorityDesc:"Each owner picks one week",priorityRule:"One week per owner",regularName:"Regular Booking",regularDesc:"Book as much as you like",regularRule:"Opens 6 months before the booking date",revealed:"Revealed",hidden:"Hidden until reveal",hiddenCalNote:"Clan and Priority bookings are not yet revealed. Only your own and Regular bookings are shown.",adminBooking:"via admin",bookOnBehalf:"Book on behalf",loading:"Loading...",emailTab:"Paste Email",manualTab:"Manual",pasteEmail:"Paste the email here...",parseEmail:"Parse Email",parsing:"Reading email...",parsedResult:"Parsed from email",noMatch:"Could not match a registered owner",weekNotFound:"Could not identify the requested week",parseError:"Could not parse the email. Please use manual entry.",confirmBooking:"Confirm Booking",parsedName:"Detected name",parsedWeek:"Requested week",feedback:"Feedback",surveyTitle:"Share your feedback",surveyIntro:"Help us improve the booking system. Your answers are anonymous.",fq1:"What features would you like to see added?",fq1placeholder:"Describe features you'd like...",fq2:"What would you change about the current design?",fq2placeholder:"Describe changes you'd suggest...",fq3:"How easy was it to navigate the booking system?",fq3a:"Very easy",fq3b:"Easy",fq3c:"Neutral",fq3d:"Difficult",fq3e:"Very difficult",fq4:"Any other comments or suggestions?",fq4placeholder:"Type your comments here...",submitFeedback:"Submit Feedback",feedbackThanks:"Thank you for your feedback!",feedbackSaved:"Your response has been saved.",submitAnother:"Submit another response",feedbackCount:"responses collected",phaseOpen:"Open from",phaseTo:"to",phaseRevealed:"Bookings will be revealed on",phaseVisibleNow:"Bookings are visible immediately",phaseOpensOn:"Opens on",phaseClosed:"Closed",phaseRevealedOn:"Bookings were revealed on",regularOpensInfo:"Each week opens 6 months before its start date",weekNotYetOpen:"Opens for booking on",weekBookedByOther:"Already booked",confirmationEmailSent:"A confirmation email will be sent to",requestCancel:"Request cancellation",pendingCancellation:"Cancellation pending",cancelRequested:"Cancellation requested",approveCancellation:"Approve",rejectCancellation:"Reject",pendingCancellations:"Pending Cancellations",cancellationsTab:"Cancellations",cancellationApproved:"Cancellation approved",cancellationRejected:"Cancellation rejected",noPendingCancellations:"No pending cancellations",statsTitle:"Booking Statistics",bookingsPerUser:"Bookings per user",bookingsPerBranch:"Bookings per branch",overallOccupancy:"Overall occupancy",bookingTimeline:"Booking timeline",totalWeeks:"Total weeks",bookedWeeks:"Booked weeks",availableWeeks:"Available weeks",occupancyRate:"Occupancy rate",userName:"User",branchLabel:"Branch",total:"Total",lastLogin:"Last login",bookingCount:"Bookings",adminOnly:"Admin access required",noData:"No data available",mon:"Mon",tue:"Tue",wed:"Wed",thu:"Thu",fri:"Fri",sat:"Sat",sun:"Sun",
  },
  nl: {
    appName:"Fargny",subtitle:"Vakantiehuis Boekingssysteem",signIn:"Inloggen",register:"Registreren",email:"E-mailadres",displayName:"Weergavenaam",password:"Wachtwoord",fillAll:"Vul alle velden in",invalidEmail:"Voer een geldig e-mailadres in",pickBranch:"Kies je stam",whichBranch:"Bij welke stam hoor je?",createAccount:"Account aanmaken",nameTaken:"E-mail al geregistreerd",invalidLogin:"Ongeldig e-mailadres of wachtwoord",signOut:"Uitloggen",weeksBooked:"weken geboekt",book:"Boeken",calendar:"Kalender",statistics:"Statistieken",bookingPhase:"Boekingsfase",year:"Jaar",myBookings:"Mijn Boekingen",noBookingsYet:"Nog geen boekingen",familyBranches:"Stammen",clickBranch:"Klik op een stam om de leden te zien",all:"Alle",noWeeks:"Geen weken beschikbaar",bookBtn:"Boeken",cancelBtn:"Annuleren",yourBooking:"Jouw boeking",branchAlreadyBooked:"Je stam heeft al een week geboekt",youAlreadyBooked:"Je hebt al een week geboekt",alreadyBookedWeek:"Je hebt deze week al geboekt!",branchBookedPhase:"Je stam heeft al een week geboekt in deze fase",youBookedPhase:"Je hebt al een week geboekt in deze fase",booked:"Geboekt",bookingCancelled:"Boeking geannuleerd",available:"Beschikbaar",doubleBooking:"Dubbele boeking",noMembers:"Nog geen leden geregistreerd",membersRegistered:"leden geregistreerd",memberRegistered:"lid geregistreerd",close:"Sluiten",admin:"Beheerder",adminTitle:"Boeking namens iemand anders",adminDesc:"Voor eigenaren die per e-mail boeken",owner:"Eigenaar",pickOwner:"Kies een eigenaar...",phase:"Fase",week:"Week",pickWeek:"Kies een week...",clanName:"Stamboeking",clanDesc:"Elke stam kiest een week",clanRule:"Een week per stam",priorityName:"Prioriteitsboeking",priorityDesc:"Elke eigenaar kiest een week",priorityRule:"Een week per eigenaar",regularName:"Reguliere Boeking",regularDesc:"Boek zoveel als je wilt",regularRule:"Opent 6 maanden voor de boekingsdatum",revealed:"Onthuld",hidden:"Verborgen tot onthulling",hiddenCalNote:"Stam- en prioriteitsboekingen zijn nog niet onthuld. Alleen je eigen en reguliere boekingen worden getoond.",adminBooking:"via beheerder",bookOnBehalf:"Namens iemand boeken",loading:"Laden...",emailTab:"Email plakken",manualTab:"Handmatig",pasteEmail:"Plak hier de email...",parseEmail:"Email lezen",parsing:"Email wordt gelezen...",parsedResult:"Uit email gehaald",noMatch:"Kon geen geregistreerde eigenaar vinden",weekNotFound:"Kon de gevraagde week niet herkennen",parseError:"Kon de email niet verwerken.",confirmBooking:"Boeking bevestigen",parsedName:"Herkende naam",parsedWeek:"Gevraagde week",feedback:"Feedback",surveyTitle:"Deel je feedback",surveyIntro:"Help ons het boekingssysteem te verbeteren. Je antwoorden zijn anoniem.",fq1:"Welke functies zou je graag toegevoegd zien?",fq1placeholder:"Beschrijf gewenste functies...",fq2:"Wat zou je veranderen aan het huidige ontwerp?",fq2placeholder:"Beschrijf suggesties voor wijzigingen...",fq3:"Hoe makkelijk was het om door het boekingssysteem te navigeren?",fq3a:"Zeer makkelijk",fq3b:"Makkelijk",fq3c:"Neutraal",fq3d:"Moeilijk",fq3e:"Zeer moeilijk",fq4:"Overige opmerkingen of suggesties?",fq4placeholder:"Typ hier je opmerkingen...",submitFeedback:"Feedback versturen",feedbackThanks:"Bedankt voor je feedback!",feedbackSaved:"Je reactie is opgeslagen.",submitAnother:"Nog een reactie versturen",feedbackCount:"reacties verzameld",phaseOpen:"Open van",phaseTo:"tot",phaseRevealed:"Boekingen worden onthuld op",phaseVisibleNow:"Boekingen zijn direct zichtbaar",phaseOpensOn:"Opent op",phaseClosed:"Gesloten",phaseRevealedOn:"Boekingen werden onthuld op",regularOpensInfo:"Elke week opent 6 maanden voor de startdatum",weekNotYetOpen:"Opent voor boeking op",weekBookedByOther:"Al geboekt",confirmationEmailSent:"Er wordt een bevestigingsmail gestuurd naar",requestCancel:"Annulering aanvragen",pendingCancellation:"Annulering in behandeling",cancelRequested:"Annulering aangevraagd",approveCancellation:"Goedkeuren",rejectCancellation:"Afwijzen",pendingCancellations:"Openstaande annuleringen",cancellationsTab:"Annuleringen",cancellationApproved:"Annulering goedgekeurd",cancellationRejected:"Annulering afgewezen",noPendingCancellations:"Geen openstaande annuleringen",statsTitle:"Boekingsstatistieken",bookingsPerUser:"Boekingen per gebruiker",bookingsPerBranch:"Boekingen per stam",overallOccupancy:"Totale bezetting",bookingTimeline:"Boekingstijdlijn",totalWeeks:"Totaal weken",bookedWeeks:"Geboekte weken",availableWeeks:"Beschikbare weken",occupancyRate:"Bezettingsgraad",userName:"Gebruiker",branchLabel:"Stam",total:"Totaal",lastLogin:"Laatste login",bookingCount:"Boekingen",adminOnly:"Beheerdertoegang vereist",noData:"Geen gegevens beschikbaar",mon:"Ma",tue:"Di",wed:"Wo",thu:"Do",fri:"Vr",sat:"Za",sun:"Zo",
  },
};

// ═══════════════════════════════════════════════════════
// WEEK UTILS
// ═══════════════════════════════════════════════════════
const MONTHS = {
  en: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
  nl: ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
};
const FULL_MONTHS = {
  en: ['January','February','March','April','May','June','July','August','September','October','November','December'],
  nl: ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'],
};

function toISO(date) {
  return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
}

function generateWeeks(year) {
  const weeks = [];
  let d = new Date(year, 0, 1);
  while (d.getDay() !== 6) d.setDate(d.getDate() + 1);
  let weekNum = 1;
  while (d.getFullYear() <= year) {
    const start = new Date(d);
    const end = new Date(d); end.setDate(end.getDate() + 6);
    if (start.getFullYear() !== year) break;
    weeks.push({ id: year + '-W' + String(weekNum).padStart(2,'0'), weekNum: weekNum, start: toISO(start), end: toISO(end), month: start.getMonth() });
    d.setDate(d.getDate() + 7); weekNum++;
  }
  return weeks;
}

function formatDate(iso, lang) {
  if (!iso) return '';
  var p = iso.split('-');
  return parseInt(p[2]) + ' ' + MONTHS[lang][parseInt(p[1])-1];
}

function formatFullDate(iso, lang) {
  if (!iso) return '';
  var p = iso.split('-');
  return parseInt(p[2]) + ' ' + MONTHS[lang][parseInt(p[1])-1] + ' ' + p[0];
}

function isWeekBookableRegular(weekStart) {
  var today = new Date();
  today.setHours(0,0,0,0);
  var start = new Date(weekStart + 'T00:00:00');
  var sixBefore = new Date(start);
  sixBefore.setMonth(sixBefore.getMonth() - 6);
  return today >= sixBefore;
}

function regularOpenDate(weekStart) {
  var start = new Date(weekStart + 'T00:00:00');
  var sixBefore = new Date(start);
  sixBefore.setMonth(sixBefore.getMonth() - 6);
  return toISO(sixBefore);
}

// ═══════════════════════════════════════════════════════
// MOCK DATA
// ═══════════════════════════════════════════════════════
const MOCK_USERS = [
  { id: 1, name: "Marielle", email: "marielle@fargny.nl", branch_id: 1, is_admin: true },
  { id: 2, name: "Jean-Pierre Fromageot", email: "jp@fargny.nl", branch_id: 1, is_admin: false },
  { id: 3, name: "Sophie Van der Grinten", email: "sophie@fargny.nl", branch_id: 2, is_admin: false },
  { id: 4, name: "Thomas Van der Grinten", email: "thomas@fargny.nl", branch_id: 2, is_admin: false },
  { id: 5, name: "Anna Stam3", email: "anna@fargny.nl", branch_id: 3, is_admin: false },
  { id: 6, name: "Peter Stam4", email: "peter@fargny.nl", branch_id: 4, is_admin: false },
];

const MOCK_PHASE_CONFIG = {
  year: 2026,
  clan_start: '2025-11-01', clan_end: '2025-12-15', clan_reveal: '2025-12-16',
  priority_start: '2025-12-15', priority_end: '2025-12-31', priority_reveal: '2026-01-01',
  regular_start: '2026-01-01',
};

const INITIAL_BOOKINGS = [
  { id: 101, week_id: '2026-W12', year: 2026, user_id: 3, branch_id: 2, phase: 'clan', admin_booked: false, user_name: 'Sophie Van der Grinten', branch_name: 'Van der Grinten', status: 'confirmed', created_at: '2025-11-05' },
  { id: 102, week_id: '2026-W28', year: 2026, user_id: 2, branch_id: 1, phase: 'clan', admin_booked: false, user_name: 'Jean-Pierre Fromageot', branch_name: 'Fromageot', status: 'confirmed', created_at: '2025-11-10' },
  { id: 103, week_id: '2026-W30', year: 2026, user_id: 3, branch_id: 2, phase: 'priority', admin_booked: false, user_name: 'Sophie Van der Grinten', branch_name: 'Van der Grinten', status: 'confirmed', created_at: '2025-12-18' },
  { id: 104, week_id: '2026-W35', year: 2026, user_id: 5, branch_id: 3, phase: 'regular', admin_booked: false, user_name: 'Anna Stam3', branch_name: 'Stam 3', status: 'confirmed', created_at: '2026-02-15' },
  { id: 105, week_id: '2026-W35', year: 2026, user_id: 6, branch_id: 4, phase: 'regular', admin_booked: true, user_name: 'Peter Stam4', branch_name: 'Stam 4', status: 'confirmed', created_at: '2026-02-20' },
];

const MOCK_BRANCHES = FAMILY_BRANCHES.map(function(b) {
  return Object.assign({}, b, { member_count: MOCK_USERS.filter(function(u) { return u.branch_id === b.id; }).length });
});

// ═══════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════
function getPhaseMeta(phase, t) {
  return {
    clan: { name: t.clanName, desc: t.clanDesc, rule: t.clanRule, icon: PHASES.clan.icon },
    priority: { name: t.priorityName, desc: t.priorityDesc, rule: t.priorityRule, icon: PHASES.priority.icon },
    regular: { name: t.regularName, desc: t.regularDesc, rule: t.regularRule, icon: PHASES.regular.icon },
  }[phase];
}

function phaseColor(phase) {
  return phase === 'clan' ? '#B85042' : phase === 'priority' ? '#C4853B' : '#4A7C59';
}

function isPhaseActive(phaseId, phaseConfig) {
  if (!phaseConfig) return true;
  var today = new Date().toISOString().split('T')[0];
  if (phaseId === 'clan') return today >= phaseConfig.clan_start && today <= phaseConfig.clan_end;
  if (phaseId === 'priority') return today >= phaseConfig.priority_start && today <= phaseConfig.priority_end;
  return today >= phaseConfig.regular_start;
}

function getPhaseName(phase, t) {
  return { clan: t.clanName, priority: t.priorityName, regular: t.regularName }[phase] || phase;
}

// ═══════════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════════
function Toast(_ref) {
  var message = _ref.message;
  if (!message) return null;
  return h('div', { style: {
    position:'fixed',top:20,right:20,background:COLORS.text,color:COLORS.white,
    padding:'12px 24px',borderRadius:10,fontSize:14,fontWeight:500,zIndex:1000,
    boxShadow:'0 4px 20px rgba(0,0,0,0.15)',animation:'fadeIn 0.3s ease',fontFamily:"'DM Sans', sans-serif",
  }}, message);
}

// ═══════════════════════════════════════════════════════
// PHASE INFO BOX
// ═══════════════════════════════════════════════════════
function PhaseInfoBox(_ref) {
  var phase = _ref.phase, phaseConfig = _ref.phaseConfig, t = _ref.t, lang = _ref.lang;
  if (!phaseConfig) return null;
  var today = new Date().toISOString().split('T')[0];
  var startDate, endDate, revealDate, isActive, isRevealed;
  if (phase === 'clan') {
    startDate = phaseConfig.clan_start; endDate = phaseConfig.clan_end; revealDate = phaseConfig.clan_reveal;
    isActive = today >= startDate && today <= endDate; isRevealed = today >= revealDate;
  } else if (phase === 'priority') {
    startDate = phaseConfig.priority_start; endDate = phaseConfig.priority_end; revealDate = phaseConfig.priority_reveal;
    isActive = today >= startDate && today <= endDate; isRevealed = today >= revealDate;
  } else {
    startDate = phaseConfig.regular_start; endDate = null; revealDate = null;
    isActive = today >= startDate; isRevealed = true;
  }
  var pc = phaseColor(phase);

  if (!isActive && today < startDate) {
    return h('div', { style: { background:'#F5F0EB',border:'1px solid #E0D8CF',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#8B7D6B',marginTop:12,lineHeight:1.5 }},
      '\u{1F512} ', t.phaseOpensOn, ' ', h('strong',null,formatFullDate(startDate,lang))
    );
  }
  if (!isActive && today > (endDate || startDate)) {
    return h('div', { style: { background:'#F5F0EB',border:'1px solid #E0D8CF',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#8B7D6B',marginTop:12,lineHeight:1.5 }},
      isRevealed && revealDate
        ? h(React.Fragment,null,'\u2705 ', t.phaseRevealedOn, ' ', h('strong',null,formatFullDate(revealDate,lang)))
        : h(React.Fragment,null,'\u{1F512} ', t.phaseClosed)
    );
  }
  return h('div', { style: { background:pc+'0A',border:'1px solid '+pc+'30',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#5A4D40',marginTop:12,lineHeight:1.6 }},
    '\u{1F4C5} ', t.phaseOpen, ' ', h('strong',null,formatFullDate(startDate,lang)),
    endDate && h(React.Fragment,null,' ',t.phaseTo,' ',h('strong',null,formatFullDate(endDate,lang))),
    revealDate && h('div',{style:{marginTop:2}},'\u{1F50D} ',t.phaseRevealed,' ',h('strong',null,formatFullDate(revealDate,lang))),
    !revealDate && h('div',{style:{marginTop:2}},'\u{1F33F} ',t.regularOpensInfo)
  );
}

// ═══════════════════════════════════════════════════════
// BRANCH MEMBERS PANEL
// ═══════════════════════════════════════════════════════
function BranchMembersPanel(_ref) {
  var branch = _ref.branch, onClose = _ref.onClose, t = _ref.t;
  var members = MOCK_USERS.filter(function(u) { return u.branch_id === branch.id; });
  return h('div', { style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'}, onClick:onClose },
    h('div', { onClick:function(e){e.stopPropagation();}, style:{background:COLORS.white,borderRadius:16,padding:28,width:360,maxHeight:'70vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'} },
      h('div', { style:{display:'flex',alignItems:'center',gap:10,marginBottom:20} },
        h('div', { style:{width:16,height:16,borderRadius:4,background:branch.color} }),
        h('h3', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display', Georgia, serif"} }, branch.name)
      ),
      members.length === 0
        ? h('p', { style:{fontSize:14,color:'#B0A090',margin:0} }, t.noMembers)
        : h('div', { style:{display:'flex',flexDirection:'column',gap:8} },
            members.map(function(m) {
              return h('div', { key:m.id, style:{display:'flex',alignItems:'center',gap:10,padding:'10px 14px',background:'#F8F5F2',borderRadius:10,borderLeft:'3px solid '+branch.color} },
                h('div', { style:{width:32,height:32,borderRadius:'50%',background:branch.color+'20',display:'flex',alignItems:'center',justifyContent:'center',fontSize:14,fontWeight:700,color:branch.color} }, m.name.charAt(0).toUpperCase()),
                h('div', null,
                  h('span', { style:{fontSize:14,fontWeight:500,color:COLORS.text} }, m.name),
                  h('br'),
                  h('span', { style:{fontSize:11,color:COLORS.muted} }, m.email),
                  m.is_admin && h('span', { style:{fontSize:10,background:'#C4853B20',color:'#C4853B',padding:'2px 6px',borderRadius:4,marginLeft:6,fontWeight:600} }, t.admin)
                )
              );
            })
          ),
      h('div', { style:{marginTop:16,fontSize:12,color:COLORS.muted} }, members.length + ' ' + (members.length!==1?t.membersRegistered:t.memberRegistered)),
      h('button', { onClick:onClose, style:{marginTop:16,width:'100%',padding:'10px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.close)
    )
  );
}

// ═══════════════════════════════════════════════════════
// ADMIN PANEL (with cancellation approval)
// ═══════════════════════════════════════════════════════
function AdminPanel(_ref) {
  var users = _ref.users, weeks = _ref.weeks, year = _ref.year, bookings = _ref.bookings, onBooked = _ref.onBooked, onApproveCancellation = _ref.onApproveCancellation, onRejectCancellation = _ref.onRejectCancellation, onClose = _ref.onClose, t = _ref.t, lang = _ref.lang;
  var _tab = useState('manual'), tab = _tab[0], setTab = _tab[1];
  var _su = useState(''), selectedUser = _su[0], setSelectedUser = _su[1];
  var _sw = useState(''), selectedWeek = _sw[0], setSelectedWeek = _sw[1];
  var _sp = useState('regular'), selectedPhase = _sp[0], setSelectedPhase = _sp[1];

  var pendingCancellations = (bookings || []).filter(function(b) { return b.status === 'pending_cancellation'; });

  var handleSubmit = function() {
    if (!selectedUser || !selectedWeek) return;
    var u = users.find(function(x) { return x.id === parseInt(selectedUser); });
    if (u) onBooked(selectedWeek, u, selectedPhase);
    onClose();
  };

  var tabBtn = function(id, label) {
    return h('button', { key:id, onClick:function(){setTab(id);}, style:{flex:1,padding:'9px 0',border:'none',borderRadius:8,background:tab===id?COLORS.white:'transparent',color:tab===id?COLORS.text:COLORS.muted,fontWeight:tab===id?600:400,cursor:'pointer',fontSize:12,boxShadow:tab===id?'0 1px 4px rgba(0,0,0,0.08)':'none',fontFamily:"'DM Sans', sans-serif"} }, label);
  };

  return h('div', { style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'}, onClick:onClose },
    h('div', { onClick:function(e){e.stopPropagation();}, style:{background:COLORS.white,borderRadius:16,padding:28,width:520,maxHeight:'85vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'} },
      h('h3', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:'0 0 6px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.adminTitle),
      h('p', { style:{fontSize:13,color:COLORS.muted,margin:'0 0 16px 0'} }, t.adminDesc),
      // Tabs
      h('div', { style:{display:'flex',gap:0,marginBottom:20,background:COLORS.bg,borderRadius:10,padding:3} },
        tabBtn('email','\u{1F4E7} '+t.emailTab),
        tabBtn('manual','\u270F\uFE0F '+t.manualTab),
        tabBtn('cancellations','\u274C '+t.cancellationsTab + (pendingCancellations.length > 0 ? ' ('+pendingCancellations.length+')' : ''))
      ),
      // EMAIL TAB
      tab === 'email' && h('div', { style:{display:'flex',flexDirection:'column',gap:14} },
        h('textarea', { placeholder:t.pasteEmail, rows:6, style:{padding:'12px 16px',border:'1.5px solid '+COLORS.border,borderRadius:10,fontSize:14,outline:'none',resize:'vertical',fontFamily:'inherit',lineHeight:1.5} }),
        h('div', { style:{padding:16,background:'#3B6B9E10',border:'1px solid #3B6B9E30',borderRadius:10,textAlign:'center',fontSize:13,color:'#3B6B9E'} }, '\u{1F916} Email parsing requires the backend API with Anthropic key. Use manual tab for this demo.'),
        h('button', { onClick:onClose, style:{padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.cancelBtn)
      ),
      // MANUAL TAB
      tab === 'manual' && h('div', { style:{display:'flex',flexDirection:'column',gap:14} },
        h('div', null,
          h('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.owner),
          h('select', { value:selectedUser, onChange:function(e){setSelectedUser(e.target.value);}, style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans', sans-serif"} },
            h('option', { value:'' }, t.pickOwner),
            users.map(function(u) { var br = FAMILY_BRANCHES.find(function(b){return b.id===u.branch_id;}); return h('option', { key:u.id, value:u.id }, u.name+' ('+((br?br.name:'?'))+')'); })
          )
        ),
        h('div', null,
          h('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.phase),
          h('div', { style:{display:'flex',gap:6} },
            PHASE_ORDER.map(function(pid) {
              var p = getPhaseMeta(pid,t);
              return h('button', { key:pid, onClick:function(){setSelectedPhase(pid);}, style:{flex:1,padding:'8px 0',border:selectedPhase===pid?'1.5px solid #B85042':'1.5px solid '+COLORS.border,borderRadius:8,background:selectedPhase===pid?'#B850420A':COLORS.white,fontSize:11,fontWeight:600,color:selectedPhase===pid?'#B85042':COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, p.icon+' '+p.name);
            })
          )
        ),
        h('div', null,
          h('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.week),
          h('select', { value:selectedWeek, onChange:function(e){setSelectedWeek(e.target.value);}, style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans', sans-serif"} },
            h('option', { value:'' }, t.pickWeek),
            weeks.map(function(w) { return h('option', { key:w.id, value:w.id }, 'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang)); })
          )
        ),
        h('div', { style:{display:'flex',gap:8,marginTop:4} },
          h('button', { onClick:onClose, style:{flex:1,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.cancelBtn),
          h('button', { onClick:handleSubmit, disabled:!selectedUser||!selectedWeek, style:{flex:1,padding:'11px 0',background:(!selectedUser||!selectedWeek)?'#D4C5B5':COLORS.success,border:'none',borderRadius:10,fontSize:14,fontWeight:600,color:'white',cursor:(!selectedUser||!selectedWeek)?'default':'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.bookBtn)
        )
      ),
      // CANCELLATIONS TAB
      tab === 'cancellations' && h('div', { style:{display:'flex',flexDirection:'column',gap:10} },
        pendingCancellations.length === 0
          ? h('div', { style:{padding:24,textAlign:'center',color:COLORS.muted,fontSize:14} }, t.noPendingCancellations)
          : pendingCancellations.map(function(b) {
              var w = weeks.find(function(wk){return wk.id===b.week_id;});
              var branch = FAMILY_BRANCHES.find(function(br){return br.id===b.branch_id;});
              return h('div', { key:b.id, style:{padding:'14px 16px',background:'#FEF2F2',border:'1px solid #B8504230',borderRadius:10,display:'flex',alignItems:'center',gap:12} },
                h('div', { style:{flex:1} },
                  h('div', { style:{fontSize:14,fontWeight:600,color:COLORS.text} }, b.user_name),
                  h('div', { style:{fontSize:12,color:COLORS.muted,marginTop:2} },
                    (branch?branch.name:'') + ' \u00B7 ' + (w?'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang):b.week_id) + ' \u00B7 ' + getPhaseName(b.phase,t)
                  )
                ),
                h('button', { onClick:function(){onApproveCancellation(b.id);}, style:{padding:'6px 14px',background:COLORS.success,border:'none',borderRadius:8,fontSize:12,color:'white',cursor:'pointer',fontWeight:600,fontFamily:"'DM Sans', sans-serif"} }, t.approveCancellation),
                h('button', { onClick:function(){onRejectCancellation(b.id);}, style:{padding:'6px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans', sans-serif"} }, t.rejectCancellation)
              );
            }),
        h('button', { onClick:onClose, style:{marginTop:8,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.close)
      )
    )
  );
}

// ═══════════════════════════════════════════════════════
// WEEK ROW (updated: regular blocking, cancellation request)
// ═══════════════════════════════════════════════════════
function WeekRow(_ref) {
  var week = _ref.week, myBooking = _ref.myBooking, allBookings = _ref.allBookings, onBook = _ref.onBook, onRequestCancel = _ref.onRequestCancel, user = _ref.user, phase = _ref.phase, clanBookedByBranch = _ref.clanBookedByBranch, userBookedPriority = _ref.userBookedPriority, phaseActive = _ref.phaseActive, t = _ref.t, lang = _ref.lang;
  var hasMyBooking = !!myBooking;
  var userBranch = FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});
  var isPending = hasMyBooking && myBooking.status === 'pending_cancellation';

  // In regular phase, check if week is booked by anyone or not yet open
  var weekBookedByOther = false;
  var weekNotOpen = false;
  if (phase === 'regular' && !hasMyBooking) {
    var confirmedBookings = (allBookings || []).filter(function(b) { return b.week_id === week.id && b.status === 'confirmed'; });
    if (confirmedBookings.length > 0) weekBookedByOther = true;
    if (!isWeekBookableRegular(week.start)) weekNotOpen = true;
  }

  var bookable = false, reason = '';
  if (!hasMyBooking && phaseActive) {
    if (phase === 'clan') { if (clanBookedByBranch) reason = t.branchAlreadyBooked; else bookable = true; }
    else if (phase === 'priority') { if (userBookedPriority) reason = t.youAlreadyBooked; else bookable = true; }
    else {
      if (weekBookedByOther) { reason = t.weekBookedByOther; }
      else if (weekNotOpen) { reason = t.weekNotYetOpen + ' ' + formatFullDate(regularOpenDate(week.start), lang); }
      else bookable = true;
    }
  }

  return h('div', { style:{display:'flex',alignItems:'center',padding:'10px 16px',borderRadius:10,background:isPending?'#FEF2F210':hasMyBooking?userBranch.color+'0D':'transparent',transition:'all 0.2s',gap:12,borderLeft:isPending?'3px solid '+COLORS.warning:hasMyBooking?'3px solid '+userBranch.color:'3px solid transparent',opacity:isPending?0.7:1} },
    h('div', { style:{width:36,textAlign:'center',fontSize:13,fontWeight:600,color:COLORS.muted} }, 'W'+week.weekNum),
    h('div', { style:{flex:1,minWidth:0} },
      h('div', { style:{fontSize:14,fontWeight:500,color:COLORS.text,textDecoration:isPending?'line-through':'none'} }, formatDate(week.start,lang)+' \u2014 '+formatDate(week.end,lang)),
      hasMyBooking && !isPending && h('div', { style:{fontSize:12,color:userBranch.color,fontWeight:600,marginTop:2} }, t.yourBooking+' \u00B7 '+getPhaseMeta(myBooking.phase,t).name+(myBooking.admin_booked?' \u00B7 '+t.adminBooking:'')),
      isPending && h('div', { style:{fontSize:12,color:COLORS.warning,fontWeight:600,marginTop:2} }, '\u23F3 '+t.pendingCancellation),
      !hasMyBooking && reason && h('div', { style:{fontSize:11,color:weekBookedByOther?COLORS.danger:'#B0A090',marginTop:2} }, reason)
    ),
    h('div', null,
      hasMyBooking && !isPending && h('button', { onClick:function(){onRequestCancel(myBooking);}, style:{padding:'6px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans', sans-serif"} }, t.requestCancel),
      isPending && h('span', { style:{fontSize:11,color:COLORS.warning,fontWeight:600} }, '\u23F3'),
      !hasMyBooking && bookable && h('button', { onClick:function(){onBook(week);}, style:{padding:'6px 14px',background:COLORS.success,border:'none',borderRadius:8,fontSize:12,color:COLORS.white,cursor:'pointer',fontWeight:600,fontFamily:"'DM Sans', sans-serif"} }, t.bookBtn)
    )
  );
}

// ═══════════════════════════════════════════════════════
// BOOKING VIEW
// ═══════════════════════════════════════════════════════
function BookingView(_ref) {
  var user = _ref.user, bookings = _ref.bookings, weeks = _ref.weeks, year = _ref.year, setYear = _ref.setYear, phase = _ref.phase, setPhase = _ref.setPhase, phaseConfig = _ref.phaseConfig, filterMonth = _ref.filterMonth, setFilterMonth = _ref.setFilterMonth, branches = _ref.branches, onSelectBranch = _ref.onSelectBranch, onShowAdmin = _ref.onShowAdmin, onBook = _ref.onBook, onRequestCancel = _ref.onRequestCancel, showToast = _ref.showToast, t = _ref.t, lang = _ref.lang;
  var myBookings = bookings.filter(function(b){return b.user_id===user.id;});
  var myBookingMap = {}; myBookings.forEach(function(b){myBookingMap[b.week_id]=b;});
  var clanBookedByBranch = bookings.find(function(b){return b.branch_id===user.branch_id&&b.phase==='clan'&&b.status==='confirmed';});
  var userBookedPriority = bookings.find(function(b){return b.user_id===user.id&&b.phase==='priority'&&b.status==='confirmed';});
  var filteredWeeks = filterMonth !== null ? weeks.filter(function(w){return w.month===filterMonth;}) : weeks;
  var userBranch = FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});

  var handleBook = function(week) { onBook(week, phase); };

  return h('div', { style:{display:'flex',maxWidth:1200,margin:'0 auto',gap:24,padding:'24px 20px'} },
    // SIDEBAR
    h('div', { style:{width:280,flexShrink:0,display:'flex',flexDirection:'column',gap:16} },
      // Phase selector
      h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        h('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.bookingPhase),
        h('div', { style:{display:'flex',flexDirection:'column',gap:6} },
          PHASE_ORDER.map(function(pid) {
            var p = getPhaseMeta(pid,t); var active = isPhaseActive(pid,phaseConfig);
            return h('button', { key:pid, onClick:function(){setPhase(pid);}, style:{display:'flex',alignItems:'flex-start',gap:10,padding:'12px 14px',border:phase===pid?'1.5px solid #B85042':'1.5px solid transparent',borderRadius:10,background:phase===pid?'#B850420A':'#F8F5F2',cursor:'pointer',textAlign:'left',opacity:active?1:0.5} },
              h('span', { style:{fontSize:18} }, p.icon),
              h('div', null,
                h('div', { style:{fontSize:13,fontWeight:600,color:phase===pid?'#B85042':COLORS.text} }, p.name),
                h('div', { style:{fontSize:11,color:COLORS.muted,marginTop:2} }, p.rule)
              )
            );
          })
        ),
        h(PhaseInfoBox, { phase:phase, phaseConfig:phaseConfig, t:t, lang:lang })
      ),
      // Year
      h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        h('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.year),
        h('div', { style:{display:'flex',gap:8} },
          [2026,2027].map(function(y) { return h('button', { key:y, onClick:function(){setYear(y);}, style:{flex:1,padding:'10px 0',border:year===y?'1.5px solid '+COLORS.success:'1.5px solid '+COLORS.border,borderRadius:8,background:year===y?COLORS.success+'0D':COLORS.white,color:year===y?COLORS.success:COLORS.muted,fontWeight:600,fontSize:14,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, y); })
        )
      ),
      // My bookings
      h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        h('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.myBookings+' ('+myBookings.length+')'),
        myBookings.length === 0
          ? h('p', { style:{fontSize:13,color:'#B0A090',margin:0} }, t.noBookingsYet)
          : h('div', { style:{display:'flex',flexDirection:'column',gap:6} },
              myBookings.map(function(b) { var w = weeks.find(function(wk){return wk.id===b.week_id;}); if(!w) return null;
                var isPending = b.status === 'pending_cancellation';
                return h('div', { key:b.id, style:{padding:'8px 12px',background:isPending?'#FEF2F2':'#F8F5F2',borderRadius:8,borderLeft:'3px solid '+(isPending?COLORS.warning:userBranch.color),opacity:isPending?0.7:1} },
                  h('div', { style:{fontSize:13,fontWeight:500,color:COLORS.text,textDecoration:isPending?'line-through':'none'} }, formatDate(w.start,lang)+' \u2014 '+formatDate(w.end,lang)),
                  isPending
                    ? h('div', { style:{fontSize:11,color:COLORS.warning,marginTop:2,fontWeight:600} }, '\u23F3 '+t.pendingCancellation)
                    : h('div', { style:{fontSize:11,color:COLORS.muted,marginTop:2} }, 'W'+w.weekNum+' \u00B7 '+getPhaseMeta(b.phase,t).name+(b.admin_booked?' \u00B7 '+t.adminBooking:''))
                );
              })
            )
      ),
      // Branches
      h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        h('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.familyBranches),
        h('div', { style:{display:'flex',flexDirection:'column',gap:4} },
          FAMILY_BRANCHES.map(function(b) {
            var bd = branches.find(function(br){return br.id===b.id;}); var mc = bd?bd.member_count:0;
            return h('button', { key:b.id, onClick:function(){onSelectBranch(b);}, style:{display:'flex',alignItems:'center',gap:8,fontSize:13,padding:'8px 10px',borderRadius:8,border:'none',background:'transparent',cursor:'pointer',width:'100%',textAlign:'left',fontFamily:"'DM Sans', sans-serif"} },
              h('div', { style:{width:10,height:10,borderRadius:3,background:b.color,flexShrink:0} }),
              h('span', { style:{color:COLORS.text,flex:1} }, b.name),
              h('span', { style:{fontSize:11,color:'#B0A090'} }, mc+'\u{1F464}')
            );
          })
        ),
        h('p', { style:{fontSize:11,color:'#B0A090',margin:'10px 0 0 0'} }, t.clickBranch)
      ),
      // Admin button
      user.is_admin && h('button', { onClick:onShowAdmin, style:{padding:'12px 16px',background:'#C4853B10',border:'1.5px solid #C4853B30',borderRadius:12,fontSize:13,fontWeight:600,color:'#C4853B',cursor:'pointer',display:'flex',alignItems:'center',gap:8,justifyContent:'center',fontFamily:"'DM Sans', sans-serif"} }, '\u{1F511} '+t.bookOnBehalf)
    ),
    // MAIN
    h('div', { style:{flex:1,minWidth:0} },
      // Month filter
      h('div', { style:{display:'flex',gap:4,marginBottom:16,flexWrap:'wrap'} },
        h('button', { onClick:function(){setFilterMonth(null);}, style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===null?COLORS.text:'#E8DFD5',color:filterMonth===null?COLORS.white:COLORS.muted,fontSize:12,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.all),
        MONTHS[lang].map(function(m,i) { if (!weeks.some(function(w){return w.month===i;})) return null;
          return h('button', { key:m, onClick:function(){setFilterMonth(i);}, style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===i?COLORS.text:'#E8DFD5',color:filterMonth===i?COLORS.white:COLORS.muted,fontSize:12,fontWeight:500,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, m);
        })
      ),
      // Phase banner
      (function() { var p = getPhaseMeta(phase,t); var pc = phaseColor(phase);
        return h('div', { style:{background:'linear-gradient(135deg, '+pc+'15, '+pc+'08)',border:'1px solid '+pc+'30',borderRadius:12,padding:'14px 20px',marginBottom:16,display:'flex',alignItems:'center',gap:12} },
          h('span', { style:{fontSize:24} }, p.icon),
          h('div', null,
            h('div', { style:{fontSize:15,fontWeight:700,color:COLORS.text} }, p.name),
            h('div', { style:{fontSize:12,color:'#5A4D40'} }, p.desc+' \u00B7 '+p.rule)
          )
        );
      })(),
      // Week list
      h('div', { style:{background:COLORS.white,borderRadius:14,padding:8,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        filteredWeeks.length === 0
          ? h('div', { style:{padding:40,textAlign:'center',color:COLORS.muted} }, t.noWeeks)
          : filteredWeeks.reduce(function(acc, week, i) {
              var prevMonth = i > 0 ? filteredWeeks[i-1].month : -1;
              if (week.month !== prevMonth) acc.push(h('div', { key:'month-'+week.month, style:{padding:'14px 16px 6px 16px',fontSize:13,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',borderTop:i>0?'1px solid #F0EBE5':'none',marginTop:i>0?4:0} }, MONTHS[lang][week.month]+' '+year));
              acc.push(h(WeekRow, { key:week.id, week:week, myBooking:myBookingMap[week.id], allBookings:bookings, onBook:handleBook, onRequestCancel:onRequestCancel, user:user, phase:phase, clanBookedByBranch:clanBookedByBranch, userBookedPriority:userBookedPriority, phaseActive:isPhaseActive(phase,phaseConfig)||user.is_admin, t:t, lang:lang }));
              return acc;
            }, [])
      )
    )
  );
}

// ═══════════════════════════════════════════════════════
// CALENDAR VIEW (monthly grid)
// ═══════════════════════════════════════════════════════
function CalendarView(_ref) {
  var user = _ref.user, bookings = _ref.bookings, weeks = _ref.weeks, year = _ref.year, phaseConfig = _ref.phaseConfig, branches = _ref.branches, onSelectBranch = _ref.onSelectBranch, t = _ref.t, lang = _ref.lang;
  var _cm = useState(function() { return new Date().getMonth(); }), calMonth = _cm[0], setCalMonth = _cm[1];
  var _cy = useState(function() { return year; }), calYear = _cy[0], setCalYear = _cy[1];
  var _popup = useState(null), popup = _popup[0], setPopup = _popup[1];

  var today = new Date().toISOString().split('T')[0];
  var clanRevealed = phaseConfig ? today >= phaseConfig.clan_reveal : false;
  var priorityRevealed = phaseConfig ? today >= phaseConfig.priority_reveal : false;
  var visibleBookings = bookings.filter(function(b) {
    if (b.status === 'pending_cancellation') return true;
    if (b.phase === 'regular') return true;
    if (b.user_id === user.id) return true;
    if (user.is_admin) return true;
    if (b.phase === 'clan') return clanRevealed;
    if (b.phase === 'priority') return priorityRevealed;
    return false;
  });
  var someHidden = !clanRevealed || !priorityRevealed;

  var goNext = function() {
    if (calMonth === 11) { setCalMonth(0); setCalYear(function(y){return y+1;}); }
    else setCalMonth(function(m){return m+1;});
  };
  var goPrev = function() {
    if (calMonth === 0) { setCalMonth(11); setCalYear(function(y){return y-1;}); }
    else setCalMonth(function(m){return m-1;});
  };

  // Build grid
  var firstOfMonth = new Date(calYear, calMonth, 1);
  var startDow = (firstOfMonth.getDay() + 6) % 7; // 0=Mon
  var daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
  var cells = [];
  for (var i = 0; i < startDow; i++) cells.push(null);
  for (var d = 1; d <= daysInMonth; d++) cells.push(d);
  while (cells.length % 7 !== 0) cells.push(null);

  // Precompute booking map for each day
  var dayBookingMap = {};
  for (var dd = 1; dd <= daysInMonth; dd++) {
    var dateStr = calYear + '-' + String(calMonth+1).padStart(2,'0') + '-' + String(dd).padStart(2,'0');
    dayBookingMap[dd] = visibleBookings.filter(function(b) {
      var week = weeks.find(function(w){return w.id===b.week_id;});
      return week && dateStr >= week.start && dateStr <= week.end;
    });
  }

  var rows = [];
  for (var ri = 0; ri < cells.length; ri += 7) {
    rows.push(cells.slice(ri, ri + 7));
  }

  var dayNames = [t.mon, t.tue, t.wed, t.thu, t.fri, t.sat, t.sun];
  var navBtnStyle = {padding:'8px 16px',border:'1.5px solid '+COLORS.border,borderRadius:8,background:COLORS.white,cursor:'pointer',fontSize:16,color:COLORS.text,fontFamily:"'DM Sans', sans-serif"};

  return h('div', { style:{maxWidth:900,margin:'0 auto',padding:'24px 20px'} },
    // Navigation
    h('div', { style:{display:'flex',alignItems:'center',justifyContent:'center',gap:16,marginBottom:20} },
      h('button', { onClick:goPrev, style:navBtnStyle }, '\u25C0'),
      h('h2', { style:{fontSize:20,fontWeight:700,color:COLORS.text,fontFamily:"'Playfair Display', Georgia, serif",margin:0,minWidth:200,textAlign:'center'} },
        FULL_MONTHS[lang][calMonth]+' '+calYear
      ),
      h('button', { onClick:goNext, style:navBtnStyle }, '\u25B6')
    ),
    someHidden && h('div', { style:{background:'#C4853B10',border:'1px solid #C4853B30',borderRadius:12,padding:'12px 18px',fontSize:13,color:'#8B6030',display:'flex',alignItems:'center',gap:8,marginBottom:16} }, '\u{1F512} '+t.hiddenCalNote),
    // Calendar grid
    h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
      // Header row
      h('div', { style:{display:'grid',gridTemplateColumns:'repeat(7, 1fr)',gap:2,marginBottom:4} },
        dayNames.map(function(name) {
          return h('div', { key:name, style:{textAlign:'center',fontSize:11,fontWeight:700,color:COLORS.muted,padding:'8px 0',textTransform:'uppercase',letterSpacing:'0.5px'} }, name);
        })
      ),
      // Day rows
      rows.map(function(row, rowIdx) {
        return h('div', { key:rowIdx, style:{display:'grid',gridTemplateColumns:'repeat(7, 1fr)',gap:2} },
          row.map(function(day, ci) {
            if (!day) return h('div', { key:'e'+ci, style:{minHeight:52} });
            var bks = dayBookingMap[day] || [];
            var hasBooking = bks.length > 0;
            var isDouble = bks.length > 1;
            var branch = hasBooking ? FAMILY_BRANCHES.find(function(b){return b.id===bks[0].branch_id;}) : null;
            var bgColor = isDouble ? COLORS.warning+'35' : hasBooking ? (branch?branch.color:'#ccc')+'25' : 'transparent';
            var hasPending = bks.some(function(b){return b.status==='pending_cancellation';});

            // Determine if this is left/right edge of a booking bar in this row
            var prevDay = ci > 0 ? row[ci-1] : null;
            var nextDay = ci < 6 ? row[ci+1] : null;
            var prevHasBooking = prevDay ? (dayBookingMap[prevDay]||[]).length > 0 : false;
            var nextHasBooking = nextDay ? (dayBookingMap[nextDay]||[]).length > 0 : false;
            var borderRadiusVal = (hasBooking ?
              (!prevHasBooking ? '8px ' : '0 ') + (!nextHasBooking ? '8px ' : '0 ') + (!nextHasBooking ? '8px ' : '0 ') + (!prevHasBooking ? '8px' : '0')
              : '4px');

            var isToday = (calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(day).padStart(2,'0')) === today;

            return h('div', { key:day, onClick:hasBooking?function(){setPopup({day:day,bookings:bks});}:null, style:{
              minHeight:52,padding:'4px 2px',textAlign:'center',cursor:hasBooking?'pointer':'default',
              background:bgColor,borderRadius:borderRadiusVal,transition:'background 0.15s',position:'relative',
              border:isToday?'2px solid '+COLORS.accent:'2px solid transparent',
            }},
              h('div', { style:{fontSize:14,fontWeight:isToday?700:500,color:hasBooking?(branch?branch.color:COLORS.text):COLORS.text} }, day),
              hasBooking && h('div', { style:{marginTop:2} },
                bks.slice(0,2).map(function(b,bi) {
                  var br = FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});
                  return h('div', { key:bi, style:{width:6,height:6,borderRadius:'50%',background:br?br.color:'#ccc',display:'inline-block',margin:'0 1px',opacity:b.status==='pending_cancellation'?0.4:1} });
                })
              ),
              isDouble && h('div', { style:{fontSize:8,color:COLORS.warning,fontWeight:700,marginTop:1} }, '\u26A0')
            );
          })
        );
      })
    ),
    // Popup
    popup && h('div', { style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.2)',zIndex:300,display:'flex',alignItems:'center',justifyContent:'center'}, onClick:function(){setPopup(null);} },
      h('div', { onClick:function(e){e.stopPropagation();}, style:{background:COLORS.white,borderRadius:14,padding:24,minWidth:280,maxWidth:400,boxShadow:'0 8px 32px rgba(0,0,0,0.15)'} },
        h('h4', { style:{fontSize:14,fontWeight:700,color:COLORS.text,margin:'0 0 12px 0'} }, popup.day+' '+FULL_MONTHS[lang][calMonth]+' '+calYear),
        h('div', { style:{display:'flex',flexDirection:'column',gap:8} },
          popup.bookings.map(function(b) {
            var br = FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});
            var w = weeks.find(function(wk){return wk.id===b.week_id;});
            var isPending = b.status === 'pending_cancellation';
            return h('div', { key:b.id, style:{display:'flex',alignItems:'center',gap:8,padding:'8px 12px',background:(br?br.color:'#ccc')+'10',borderRadius:8,borderLeft:'3px solid '+(br?br.color:'#ccc'),opacity:isPending?0.6:1} },
              h('div', null,
                h('div', { style:{fontSize:13,fontWeight:600,color:br?br.color:'#333',textDecoration:isPending?'line-through':'none'} }, b.user_name+(b.user_id===user.id?' \u2713':'')),
                h('div', { style:{fontSize:11,color:COLORS.muted} }, (br?br.name:'')+' \u00B7 '+getPhaseName(b.phase,t)),
                isPending && h('div', { style:{fontSize:10,color:COLORS.warning,fontWeight:600,marginTop:2} }, '\u23F3 '+t.pendingCancellation),
                w && h('div', { style:{fontSize:10,color:'#B0A090',marginTop:2} }, 'W'+w.weekNum+': '+formatDate(w.start,lang)+' \u2013 '+formatDate(w.end,lang))
              )
            );
          })
        ),
        h('button', { onClick:function(){setPopup(null);}, style:{marginTop:14,width:'100%',padding:'10px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.close)
      )
    ),
    // Branch legend
    h('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)',marginTop:16} },
      h('h4', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.familyBranches),
      h('div', { style:{display:'flex',flexWrap:'wrap',gap:8} },
        FAMILY_BRANCHES.map(function(b) { var bd = branches.find(function(br){return br.id===b.id;}); var mc = bd?bd.member_count:0;
          return h('button', { key:b.id, onClick:function(){onSelectBranch(b);}, style:{display:'flex',alignItems:'center',gap:6,fontSize:13,padding:'6px 12px',borderRadius:8,border:'1.5px solid #E8DFD5',background:COLORS.white,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} },
            h('div', { style:{width:10,height:10,borderRadius:3,background:b.color} }),
            h('span', { style:{color:COLORS.text} }, b.name),
            h('span', { style:{fontSize:11,color:COLORS.muted} }, mc+'\u{1F464}')
          );
        })
      )
    )
  );
}

// ═══════════════════════════════════════════════════════
// STATISTICS VIEW (admin only)
// ═══════════════════════════════════════════════════════
function StatisticsView(_ref) {
  var user = _ref.user, bookings = _ref.bookings, weeks = _ref.weeks, year = _ref.year, userStats = _ref.userStats, t = _ref.t, lang = _ref.lang;

  if (!user.is_admin) {
    return h('div', { style:{maxWidth:600,margin:'0 auto',padding:'60px 20px',textAlign:'center'} },
      h('div', { style:{fontSize:48,marginBottom:16} }, '\u{1F512}'),
      h('div', { style:{fontSize:16,color:COLORS.muted} }, t.adminOnly)
    );
  }

  var confirmedBookings = bookings.filter(function(b){return b.status==='confirmed';});

  // Bookings per user
  var perUser = {};
  confirmedBookings.forEach(function(b) {
    if (!perUser[b.user_name]) perUser[b.user_name] = { clan:0, priority:0, regular:0, total:0, branch_id:b.branch_id };
    perUser[b.user_name][b.phase]++;
    perUser[b.user_name].total++;
  });

  // Bookings per branch
  var perBranch = {};
  FAMILY_BRANCHES.forEach(function(br) { perBranch[br.name] = 0; });
  confirmedBookings.forEach(function(b) {
    var br = FAMILY_BRANCHES.find(function(x){return x.id===b.branch_id;});
    if (br) perBranch[br.name]++;
  });
  var maxBranchCount = Math.max.apply(null, Object.values(perBranch).concat([1]));

  // Occupancy
  var totalWeeks = weeks.length;
  var bookedWeekIds = {};
  confirmedBookings.forEach(function(b) { bookedWeekIds[b.week_id] = true; });
  var bookedCount = Object.keys(bookedWeekIds).length;
  var occupancyPct = totalWeeks > 0 ? Math.round(bookedCount / totalWeeks * 100) : 0;

  // Timeline: group by creation month
  var timeline = {};
  MONTHS[lang].forEach(function(m,i) { timeline[i] = 0; });
  confirmedBookings.forEach(function(b) {
    if (b.created_at) {
      var m = parseInt(b.created_at.split('-')[1]) - 1;
      timeline[m]++;
    }
  });
  var maxTimeline = Math.max.apply(null, Object.values(timeline).concat([1]));

  var cardStyle = {background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)',marginBottom:16};
  var headStyle = {fontSize:13,fontWeight:700,color:COLORS.text,margin:'0 0 14px 0',fontFamily:"'Playfair Display', Georgia, serif"};
  var thStyle = {fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',padding:'8px 10px',textAlign:'left',borderBottom:'1px solid '+COLORS.border};
  var tdStyle = {fontSize:13,padding:'8px 10px',borderBottom:'1px solid #F0EBE5',color:COLORS.text};

  return h('div', { style:{maxWidth:900,margin:'0 auto',padding:'24px 20px'} },
    h('h2', { style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 20px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, '\u{1F4CA} '+t.statsTitle+' '+year),

    // OCCUPANCY
    h('div', { style:cardStyle },
      h('h3', { style:headStyle }, t.overallOccupancy),
      h('div', { style:{display:'flex',gap:20,alignItems:'center',flexWrap:'wrap'} },
        h('div', { style:{width:100,height:100,borderRadius:'50%',background:'conic-gradient('+COLORS.success+' '+occupancyPct+'%, #E8DFD5 0)',display:'flex',alignItems:'center',justifyContent:'center',position:'relative'} },
          h('div', { style:{width:72,height:72,borderRadius:'50%',background:COLORS.white,display:'flex',alignItems:'center',justifyContent:'center',fontSize:20,fontWeight:700,color:COLORS.success} }, occupancyPct+'%')
        ),
        h('div', { style:{display:'flex',flexDirection:'column',gap:4} },
          h('div', { style:{fontSize:13,color:COLORS.text} }, h('strong',null,bookedCount), ' '+t.bookedWeeks),
          h('div', { style:{fontSize:13,color:COLORS.text} }, h('strong',null,totalWeeks-bookedCount), ' '+t.availableWeeks),
          h('div', { style:{fontSize:13,color:COLORS.muted} }, t.totalWeeks+': '+totalWeeks)
        )
      )
    ),

    // BOOKINGS PER USER
    h('div', { style:cardStyle },
      h('h3', { style:headStyle }, t.bookingsPerUser),
      h('table', { style:{width:'100%',borderCollapse:'collapse'} },
        h('thead', null,
          h('tr', null,
            h('th', { style:thStyle }, t.userName),
            h('th', { style:thStyle }, t.branchLabel),
            h('th', { style:Object.assign({},thStyle,{textAlign:'center'}) }, t.clanName),
            h('th', { style:Object.assign({},thStyle,{textAlign:'center'}) }, t.priorityName),
            h('th', { style:Object.assign({},thStyle,{textAlign:'center'}) }, t.regularName),
            h('th', { style:Object.assign({},thStyle,{textAlign:'center'}) }, t.total),
            h('th', { style:thStyle }, t.lastLogin)
          )
        ),
        h('tbody', null,
          Object.entries(perUser).map(function(entry) {
            var name = entry[0], data = entry[1];
            var br = FAMILY_BRANCHES.find(function(x){return x.id===data.branch_id;});
            var stats = userStats[name] || {};
            return h('tr', { key:name },
              h('td', { style:tdStyle }, name),
              h('td', { style:tdStyle }, h('span',{style:{display:'inline-flex',alignItems:'center',gap:4}}, h('div',{style:{width:8,height:8,borderRadius:2,background:br?br.color:'#ccc'}}), br?br.name:'?')),
              h('td', { style:Object.assign({},tdStyle,{textAlign:'center'}) }, data.clan||'-'),
              h('td', { style:Object.assign({},tdStyle,{textAlign:'center'}) }, data.priority||'-'),
              h('td', { style:Object.assign({},tdStyle,{textAlign:'center'}) }, data.regular||'-'),
              h('td', { style:Object.assign({},tdStyle,{textAlign:'center',fontWeight:700}) }, data.total),
              h('td', { style:Object.assign({},tdStyle,{fontSize:11,color:COLORS.muted}) }, stats.lastLogin||'-')
            );
          })
        )
      ),
      Object.keys(perUser).length === 0 && h('div',{style:{padding:16,textAlign:'center',color:COLORS.muted,fontSize:13}},t.noData)
    ),

    // BOOKINGS PER BRANCH (bar chart)
    h('div', { style:cardStyle },
      h('h3', { style:headStyle }, t.bookingsPerBranch),
      h('div', { style:{display:'flex',flexDirection:'column',gap:8} },
        FAMILY_BRANCHES.map(function(br) {
          var count = perBranch[br.name] || 0;
          var pct = maxBranchCount > 0 ? Math.round(count / maxBranchCount * 100) : 0;
          return h('div', { key:br.id, style:{display:'flex',alignItems:'center',gap:10} },
            h('div', { style:{width:120,fontSize:12,fontWeight:500,color:COLORS.text,textAlign:'right'} }, br.name),
            h('div', { style:{flex:1,background:'#F0EBE5',borderRadius:6,height:24,overflow:'hidden'} },
              h('div', { style:{width:pct+'%',height:'100%',background:br.color,borderRadius:6,transition:'width 0.3s',minWidth:count>0?20:0} })
            ),
            h('div', { style:{width:30,fontSize:13,fontWeight:600,color:br.color,textAlign:'center'} }, count)
          );
        })
      )
    ),

    // BOOKING TIMELINE
    h('div', { style:cardStyle },
      h('h3', { style:headStyle }, t.bookingTimeline),
      h('div', { style:{display:'flex',alignItems:'flex-end',gap:4,height:120,padding:'0 4px'} },
        MONTHS[lang].map(function(m,i) {
          var count = timeline[i] || 0;
          var pct = maxTimeline > 0 ? Math.round(count / maxTimeline * 100) : 0;
          return h('div', { key:m, style:{flex:1,display:'flex',flexDirection:'column',alignItems:'center',gap:4} },
            h('div', { style:{fontSize:10,fontWeight:600,color:COLORS.text} }, count > 0 ? count : ''),
            h('div', { style:{width:'100%',background:count>0?COLORS.success:'#E8DFD5',borderRadius:'4px 4px 0 0',height:Math.max(pct,4)+'%',transition:'height 0.3s',minHeight:4} }),
            h('div', { style:{fontSize:9,color:COLORS.muted,marginTop:4} }, m)
          );
        })
      )
    )
  );
}

// ═══════════════════════════════════════════════════════
// FEEDBACK VIEW (new questions)
// ═══════════════════════════════════════════════════════
function FeedbackView(_ref) {
  var t = _ref.t, lang = _ref.lang, user = _ref.user;
  var _fq1 = useState(''), fq1 = _fq1[0], setFq1 = _fq1[1];
  var _fq2 = useState(''), fq2 = _fq2[0], setFq2 = _fq2[1];
  var _fq3 = useState(''), fq3 = _fq3[0], setFq3 = _fq3[1];
  var _fq4 = useState(''), fq4 = _fq4[0], setFq4 = _fq4[1];
  var _sub = useState(false), submitted = _sub[0], setSubmitted = _sub[1];
  var _fc = useState(3), feedbackCount = _fc[0];

  var qStyle = {fontSize:15,fontWeight:600,color:'#2C1810',marginBottom:10};
  var taStyle = {width:'100%',padding:'12px 16px',border:'1.5px solid '+COLORS.border,borderRadius:10,fontSize:14,outline:'none',resize:'vertical',fontFamily:'inherit',lineHeight:1.5,boxSizing:'border-box'};
  var radioStyle = function(sel) {
    return {padding:'10px 16px',border:sel?'2px solid '+COLORS.success:'2px solid #E8DFD5',borderRadius:10,background:sel?COLORS.success+'0D':COLORS.white,cursor:'pointer',fontSize:14,fontWeight:sel?600:400,color:sel?COLORS.success:COLORS.text,textAlign:'left',width:'100%',transition:'all 0.15s',fontFamily:"'DM Sans', sans-serif"};
  };

  if (submitted) return h('div', { style:{maxWidth:600,margin:'0 auto',padding:'40px 20px',textAlign:'center'} },
    h('div', { style:{background:COLORS.white,borderRadius:16,padding:40,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
      h('div', { style:{fontSize:48,marginBottom:16} }, '\u{1F389}'),
      h('h2', { style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 8px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.feedbackThanks),
      h('p', { style:{fontSize:14,color:COLORS.muted,margin:'0 0 24px 0'} }, t.feedbackSaved),
      h('button', { onClick:function(){setSubmitted(false);setFq1('');setFq2('');setFq3('');setFq4('');}, style:{padding:'12px 28px',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.submitAnother)
    ));

  return h('div', { style:{maxWidth:600,margin:'0 auto',padding:'24px 20px'} },
    h('div', { style:{background:COLORS.white,borderRadius:16,padding:32,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
      h('div', { style:{textAlign:'center',marginBottom:28} },
        h('div', { style:{fontSize:32,marginBottom:8} }, '\u{1F4DD}'),
        h('h2', { style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 8px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.surveyTitle),
        h('p', { style:{fontSize:14,color:COLORS.muted,margin:0,lineHeight:1.5} }, t.surveyIntro)
      ),
      h('div', { style:{display:'flex',flexDirection:'column',gap:28} },
        // Q1: free text
        h('div', null,
          h('div', { style:qStyle }, '1. '+t.fq1),
          h('textarea', { value:fq1, onChange:function(e){setFq1(e.target.value);}, placeholder:t.fq1placeholder, rows:3, style:taStyle })
        ),
        // Q2: free text
        h('div', null,
          h('div', { style:qStyle }, '2. '+t.fq2),
          h('textarea', { value:fq2, onChange:function(e){setFq2(e.target.value);}, placeholder:t.fq2placeholder, rows:3, style:taStyle })
        ),
        // Q3: scale
        h('div', null,
          h('div', { style:qStyle }, '3. '+t.fq3),
          h('div', { style:{display:'flex',flexDirection:'column',gap:6} },
            [['a',t.fq3a],['b',t.fq3b],['c',t.fq3c],['d',t.fq3d],['e',t.fq3e]].map(function(pair) {
              var v = pair[0], label = pair[1];
              return h('button', { key:v, onClick:function(){setFq3(v);}, style:radioStyle(fq3===v) }, (fq3===v?'\u25C9':'\u25CB')+' '+label);
            })
          )
        ),
        // Q4: free text
        h('div', null,
          h('div', { style:qStyle }, '4. '+t.fq4),
          h('textarea', { value:fq4, onChange:function(e){setFq4(e.target.value);}, placeholder:t.fq4placeholder, rows:3, style:taStyle })
        ),
        // Submit
        h('button', { onClick:function(){setSubmitted(true);}, disabled:!fq3, style:{padding:'14px 0',background:!fq3?'#D4C5B5':COLORS.success,border:'none',borderRadius:12,fontSize:15,fontWeight:600,color:COLORS.white,cursor:!fq3?'default':'pointer',marginTop:8,fontFamily:"'DM Sans', sans-serif"} }, t.submitFeedback)
      )
    ),
    feedbackCount>0 && h('div',{style:{textAlign:'center',marginTop:16,fontSize:12,color:'#B0A090'}},feedbackCount+' '+t.feedbackCount)
  );
}

// ═══════════════════════════════════════════════════════
// LOGIN SCREEN (email + display name)
// ═══════════════════════════════════════════════════════
function LoginScreen(_ref) {
  var t = _ref.t, lang = _ref.lang, onLangToggle = _ref.onLangToggle, onLogin = _ref.onLogin;
  var _mode = useState('login'), mode = _mode[0], setMode = _mode[1];
  var _email = useState(''), email = _email[0], setEmail = _email[1];
  var _name = useState(''), name = _name[0], setName = _name[1];
  var _pw = useState(''), password = _pw[0], setPassword = _pw[1];
  var _br = useState(''), branchId = _br[0], setBranchId = _br[1];
  var _err = useState(''), error = _err[0], setError = _err[1];

  var handleSubmit = function(e) {
    e.preventDefault(); setError('');
    if (!email.trim()||!password.trim()) return setError(t.fillAll);
    if (!email.includes('@')) return setError(t.invalidEmail);
    if (mode==='register'&&!name.trim()) return setError(t.fillAll);
    if (mode==='register'&&!branchId) return setError(t.pickBranch);
    var isAdmin = email.toLowerCase().includes('marielle');
    var bid = mode==='register' ? parseInt(branchId) : 1;
    var displayName = mode==='register' ? name.trim() : email.split('@')[0];
    onLogin({ id: Date.now(), name: displayName, email: email.trim(), branch_id: bid, is_admin: isAdmin });
  };

  var inputStyle = {width:'100%',padding:'12px 16px',border:'1px solid '+COLORS.border,borderRadius:8,fontSize:14,fontFamily:"'DM Sans', sans-serif",color:COLORS.text,outline:'none',marginBottom:12,boxSizing:'border-box'};

  return h('div', { style:{minHeight:'100vh',display:'flex',alignItems:'center',justifyContent:'center',background:COLORS.bg,fontFamily:"'DM Sans', sans-serif",padding:20} },
    h('div', { style:{background:COLORS.white,borderRadius:16,padding:'48px 40px',maxWidth:420,width:'100%',boxShadow:'0 4px 24px rgba(44,24,16,0.08)',position:'relative'} },
      h('button', { onClick:onLangToggle, style:{position:'absolute',top:16,right:16,background:'none',border:'1px solid '+COLORS.border,borderRadius:8,padding:'4px 12px',cursor:'pointer',fontSize:13,color:COLORS.muted,fontFamily:"'DM Sans', sans-serif"} }, lang==='en'?'NL':'EN'),
      h('h1', { style:{fontFamily:"'Playfair Display', serif",fontSize:32,fontWeight:700,color:COLORS.text,margin:'0 0 4px 0',textAlign:'center'} }, t.appName),
      h('p', { style:{fontSize:14,color:COLORS.muted,margin:'0 0 32px 0',textAlign:'center'} }, t.subtitle),
      h('div', { style:{display:'flex',gap:0,marginBottom:24,borderBottom:'2px solid '+COLORS.border} },
        ['login','register'].map(function(m) { return h('button', { key:m, onClick:function(){setMode(m);setError('');}, style:{flex:1,padding:'10px 16px',background:'none',border:'none',borderBottom:'2px solid '+(mode===m?COLORS.accent:'transparent'),marginBottom:-2,cursor:'pointer',fontSize:14,fontWeight:600,color:mode===m?COLORS.text:COLORS.muted,fontFamily:"'DM Sans', sans-serif"} }, m==='login'?t.signIn:t.register); })
      ),
      h('form', { onSubmit:handleSubmit },
        error && h('div', { style:{background:'#FEF2F2',color:COLORS.danger,padding:'10px 14px',borderRadius:8,fontSize:13,marginBottom:12} }, error),
        h('input', { style:inputStyle, type:'email', placeholder:t.email, value:email, onChange:function(e){setEmail(e.target.value);} }),
        mode==='register' && h('input', { style:inputStyle, type:'text', placeholder:t.displayName, value:name, onChange:function(e){setName(e.target.value);} }),
        h('input', { style:inputStyle, type:'password', placeholder:t.password, value:password, onChange:function(e){setPassword(e.target.value);} }),
        mode==='register' && h(React.Fragment, null,
          h('label', { style:{display:'block',fontSize:13,fontWeight:600,color:COLORS.muted,marginBottom:6} }, t.whichBranch),
          h('select', { style:Object.assign({},inputStyle,{cursor:'pointer',background:COLORS.white}), value:branchId, onChange:function(e){setBranchId(e.target.value);} },
            h('option', { value:'' }, t.pickBranch),
            FAMILY_BRANCHES.map(function(b) { return h('option', { key:b.id, value:b.id }, b.name); })
          )
        ),
        h('button', { type:'submit', style:{width:'100%',padding:12,background:COLORS.accent,color:COLORS.white,border:'none',borderRadius:8,fontSize:15,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans', sans-serif",marginTop:8} }, mode==='login'?t.signIn:t.createAccount),
        h('p', { style:{fontSize:11,color:'#B0A090',textAlign:'center',marginTop:16} }, 'Demo: Enter any email/password. Include "marielle" in email for admin access.')
      )
    )
  );
}

// ═══════════════════════════════════════════════════════
// MAIN APP
// ═══════════════════════════════════════════════════════
function App() {
  var _lang = useState(localStorage.getItem('fargny_lang')||'nl'), lang = _lang[0], setLang = _lang[1];
  var _user = useState(null), user = _user[0], setUser = _user[1];
  var _view = useState('book'), view = _view[0], setView = _view[1];
  var _year = useState(2026), year = _year[0], setYear = _year[1];
  var _phase = useState('regular'), phase = _phase[0], setPhase = _phase[1];
  var _bookings = useState(INITIAL_BOOKINGS), bookings = _bookings[0], setBookings = _bookings[1];
  var _fm = useState(null), filterMonth = _fm[0], setFilterMonth = _fm[1];
  var _toast = useState(null), toast = _toast[0], setToast = _toast[1];
  var _sb = useState(null), selectedBranch = _sb[0], setSelectedBranch = _sb[1];
  var _sap = useState(false), showAdminPanel = _sap[0], setShowAdminPanel = _sap[1];
  var _userStats = useState({}), userStats = _userStats[0], setUserStats = _userStats[1];

  var t = T[lang] || T.en;
  var weeks = generateWeeks(year);
  var phaseConfig = MOCK_PHASE_CONFIG;

  var toggleLang = function() { var next = lang==='en'?'nl':'en'; setLang(next); localStorage.setItem('fargny_lang',next); };
  var showToast = function(msg) { setToast(msg); setTimeout(function(){setToast(null);},3500); };
  var logout = function() { setUser(null); };

  // Track user login stats
  var handleLogin = function(userData) {
    setUser(userData);
    setUserStats(function(prev) {
      var updated = Object.assign({}, prev);
      updated[userData.name] = Object.assign({}, updated[userData.name] || {}, {
        lastLogin: new Date().toLocaleString(),
        bookingCount: (updated[userData.name]||{}).bookingCount || 0
      });
      return updated;
    });
  };

  var handleBook = function(week, bookingPhase) {
    var branch = FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;});
    var newBooking = { id: Date.now(), week_id: week.id, year: year, user_id: user.id, branch_id: user.branch_id, phase: bookingPhase, admin_booked: false, user_name: user.name, branch_name: branch?branch.name:'', status: 'confirmed', created_at: toISO(new Date()) };
    setBookings(function(prev) { return prev.concat([newBooking]); });
    // Update user stats
    setUserStats(function(prev) {
      var updated = Object.assign({}, prev);
      var s = updated[user.name] || { lastLogin: new Date().toLocaleString(), bookingCount: 0 };
      s.bookingCount++;
      updated[user.name] = s;
      return updated;
    });
    showToast(t.booked+': '+formatDate(week.start,lang)+' \u2014 '+formatDate(week.end,lang)+'\n'+t.confirmationEmailSent+' '+(user.email||''));
  };

  var handleRequestCancel = function(booking) {
    setBookings(function(prev) {
      return prev.map(function(b) {
        if (b.id === booking.id) return Object.assign({}, b, { status: 'pending_cancellation' });
        return b;
      });
    });
    showToast(t.cancelRequested);
  };

  var handleApproveCancellation = function(bookingId) {
    setBookings(function(prev) { return prev.filter(function(b){return b.id!==bookingId;}); });
    showToast(t.cancellationApproved);
  };

  var handleRejectCancellation = function(bookingId) {
    setBookings(function(prev) {
      return prev.map(function(b) {
        if (b.id === bookingId) return Object.assign({}, b, { status: 'confirmed' });
        return b;
      });
    });
    showToast(t.cancellationRejected);
  };

  var handleAdminBook = function(weekId, targetUser, bookingPhase) {
    var w = weeks.find(function(wk){return wk.id===weekId;});
    var branch = FAMILY_BRANCHES.find(function(b){return b.id===targetUser.branch_id;});
    var newBooking = { id: Date.now(), week_id: weekId, year: year, user_id: targetUser.id, branch_id: targetUser.branch_id, phase: bookingPhase, admin_booked: true, user_name: targetUser.name, branch_name: branch?branch.name:'', status: 'confirmed', created_at: toISO(new Date()) };
    setBookings(function(prev) { return prev.concat([newBooking]); });
    showToast(t.booked+': '+targetUser.name+' - '+(w?formatDate(w.start,lang):weekId));
  };

  if (!user) return h(LoginScreen, { t:t, lang:lang, onLangToggle:toggleLang, onLogin:handleLogin });

  var userBranch = FAMILY_BRANCHES.find(function(b){return b.id===user.branch_id;}) || FAMILY_BRANCHES[0];
  var yearBookings = bookings.filter(function(b){return b.year===year;});
  var totalBooked = yearBookings.filter(function(b){return b.status==='confirmed';}).length;
  var pendingCount = yearBookings.filter(function(b){return b.status==='pending_cancellation';}).length;

  var navItems = [
    { id:'book', label:t.book, emoji:'\u{1F4DD}' },
    { id:'calendar', label:t.calendar, emoji:'\u{1F4C5}' },
    { id:'feedback', label:t.feedback, emoji:'\u{1F4CB}' },
  ];
  if (user.is_admin) {
    navItems.push({ id:'statistics', label:t.statistics, emoji:'\u{1F4CA}' });
  }

  return h('div', { style:{minHeight:'100vh',background:COLORS.bg,fontFamily:"'DM Sans', 'Segoe UI', sans-serif"} },
    selectedBranch && h(BranchMembersPanel, { branch:selectedBranch, onClose:function(){setSelectedBranch(null);}, t:t }),
    showAdminPanel && h(AdminPanel, { users:MOCK_USERS, weeks:weeks, year:year, bookings:yearBookings, onBooked:handleAdminBook, onApproveCancellation:handleApproveCancellation, onRejectCancellation:handleRejectCancellation, onClose:function(){setShowAdminPanel(false);}, t:t, lang:lang }),
    h(Toast, { message:toast }),

    // HEADER
    h('div', { style:{background:COLORS.white,borderBottom:'1px solid '+COLORS.border,padding:'12px 20px',display:'flex',alignItems:'center',justifyContent:'space-between',position:'sticky',top:0,zIndex:100,flexWrap:'wrap',gap:8} },
      h('div', { style:{display:'flex',alignItems:'center',gap:10} },
        h('span', { style:{fontSize:22} }, '\u{1F3E1}'),
        h('div', null,
          h('h1', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display', Georgia, serif"} }, 'Fargny'),
          h('p', { style:{fontSize:11,color:COLORS.muted,margin:0} }, year+' \u00B7 '+totalBooked+' '+t.weeksBooked+(pendingCount>0?' \u00B7 '+pendingCount+' pending':''))
        )
      ),
      h('div', { style:{display:'flex',gap:0,background:COLORS.bg,borderRadius:10,padding:3} },
        navItems.map(function(v) {
          return h('button', { key:v.id, onClick:function(){setView(v.id);}, style:{padding:'7px 16px',border:'none',borderRadius:8,background:view===v.id?COLORS.white:'transparent',color:view===v.id?COLORS.text:COLORS.muted,fontWeight:view===v.id?600:400,cursor:'pointer',fontSize:13,fontFamily:"'DM Sans', sans-serif",boxShadow:view===v.id?'0 1px 4px rgba(0,0,0,0.08)':'none'} }, v.emoji+' '+v.label);
        })
      ),
      h('div', { style:{display:'flex',alignItems:'center',gap:10} },
        h('button', { onClick:toggleLang, style:{padding:'6px 12px',border:'1.5px solid '+COLORS.border,borderRadius:8,background:COLORS.white,fontSize:12,fontWeight:600,color:COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, lang==='en'?'\u{1F1F3}\u{1F1F1} NL':'\u{1F1EC}\u{1F1E7} EN'),
        h('div', { style:{textAlign:'right'} },
          h('div', { style:{fontSize:13,fontWeight:600,color:COLORS.text} }, user.name),
          h('div', { style:{fontSize:11,color:userBranch.color,fontWeight:600} }, userBranch.name)
        ),
        h('button', { onClick:logout, style:{padding:'7px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans', sans-serif"} }, t.signOut)
      )
    ),

    // VIEWS
    view === 'book' && h(BookingView, { user:user, bookings:yearBookings, weeks:weeks, year:year, setYear:function(y){setYear(y);setFilterMonth(null);}, phase:phase, setPhase:setPhase, phaseConfig:phaseConfig, filterMonth:filterMonth, setFilterMonth:setFilterMonth, branches:MOCK_BRANCHES, onSelectBranch:setSelectedBranch, onShowAdmin:function(){setShowAdminPanel(true);}, onBook:handleBook, onRequestCancel:handleRequestCancel, showToast:showToast, t:t, lang:lang }),
    view === 'calendar' && h(CalendarView, { user:user, bookings:yearBookings, weeks:weeks, year:year, phaseConfig:phaseConfig, branches:MOCK_BRANCHES, onSelectBranch:setSelectedBranch, t:t, lang:lang }),
    view === 'feedback' && h(FeedbackView, { t:t, lang:lang, user:user }),
    view === 'statistics' && h(StatisticsView, { user:user, bookings:yearBookings, weeks:weeks, year:year, userStats:userStats, t:t, lang:lang })
  );
}

// ═══════════════════════════════════════════════════════
// MOUNT
// ═══════════════════════════════════════════════════════
var root = ReactDOM.createRoot(document.getElementById('fargny-booking-root'));
root.render(h(App));
