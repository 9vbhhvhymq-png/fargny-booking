const { useState, useEffect, useCallback } = React;

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
    appName:"Fargny",subtitle:"Holiday House Booking",signIn:"Sign In",register:"Register",fullName:"Your full name",password:"Password",fillAll:"Please fill in all fields",pickBranch:"Please select your family branch",whichBranch:"Which family branch do you belong to?",createAccount:"Create Account",nameTaken:"Name already taken",invalidLogin:"Invalid name or password",signOut:"Sign Out",weeksBooked:"weeks booked",book:"Book",calendar:"Calendar",bookingPhase:"Booking Phase",year:"Year",myBookings:"My Bookings",noBookingsYet:"No bookings yet",familyBranches:"Family Branches",clickBranch:"Click a branch to see its members",all:"All",noWeeks:"No weeks available",bookBtn:"Book",cancelBtn:"Cancel",yourBooking:"Your booking",branchAlreadyBooked:"Your branch already booked a week",youAlreadyBooked:"You already booked a week",alreadyBookedWeek:"You already booked this week!",branchBookedPhase:"Your branch already booked a week this phase",youBookedPhase:"You already booked a week this phase",booked:"Booked",bookingCancelled:"Booking cancelled",available:"Available",doubleBooking:"Double booking",noMembers:"No members registered yet",membersRegistered:"members registered",memberRegistered:"member registered",close:"Close",admin:"Admin",adminTitle:"Book on behalf of someone",adminDesc:"For owners who book via email",owner:"Owner",pickOwner:"Choose an owner...",phase:"Phase",week:"Week",pickWeek:"Choose a week...",clanName:"Clan Booking",clanDesc:"Each family branch picks one week",clanRule:"One week per family branch",priorityName:"Priority Booking",priorityDesc:"Each owner picks one week",priorityRule:"One week per owner",regularName:"Regular Booking",regularDesc:"Book as much as you like",regularRule:"Unlimited, first come first serve",revealed:"Revealed",hidden:"Hidden until reveal",hiddenCalNote:"Clan and Priority bookings are not yet revealed. Only your own and Regular bookings are shown.",adminBooking:"via admin",bookOnBehalf:"Book on behalf",loading:"Loading...",emailTab:"Paste Email",manualTab:"Manual",pasteEmail:"Paste the email here...",parseEmail:"Parse Email",parsing:"Reading email...",parsedResult:"Parsed from email",noMatch:"Could not match a registered owner",weekNotFound:"Could not identify the requested week",parseError:"Could not parse the email. Please use manual entry.",confirmBooking:"Confirm Booking",parsedName:"Detected name",parsedWeek:"Requested week",feedback:"Feedback",surveyTitle:"What do you think?",surveyIntro:"Help us improve the booking system. Your answers are anonymous.",q1:"Overall, what do you think of this booking tool?",q1a:"Love it",q1b:"Looks good, needs some tweaks",q1c:"I have concerns",q1d:"I prefer the current email system",q2:"Would you use this tool to book your weeks yourself?",q2a:"Yes, definitely",q2b:"Yes, but I'd also want the email option",q2c:"No, I prefer booking via email",q3:"Which features are most important to you?",q3a:"Seeing which weeks are available",q3b:"Seeing who booked which week (calendar)",q3c:"Being able to book without contacting Marielle",q3d:"Dutch language support",q3e:"Simple and easy to use",q4:"Is there anything you'd like to change or add?",q4placeholder:"Type your suggestions here...",q5:"How comfortable are you with using websites/apps?",q5a:"Very comfortable",q5b:"Somewhat comfortable",q5c:"I need it to be very simple",q5d:"I'd rather not use a website",submitFeedback:"Submit Feedback",feedbackThanks:"Thank you for your feedback!",feedbackSaved:"Your response has been saved.",submitAnother:"Submit another response",feedbackCount:"responses collected",phaseOpen:"Open from",phaseTo:"to",phaseRevealed:"Bookings will be revealed on",phaseVisibleNow:"Bookings are visible immediately",phaseOpensOn:"Opens on",phaseClosed:"Closed",phaseRevealedOn:"Bookings were revealed on",
  },
  nl: {
    appName:"Fargny",subtitle:"Vakantiehuis Boekingssysteem",signIn:"Inloggen",register:"Registreren",fullName:"Je volledige naam",password:"Wachtwoord",fillAll:"Vul alle velden in",pickBranch:"Kies je stam",whichBranch:"Bij welke stam hoor je?",createAccount:"Account aanmaken",nameTaken:"Naam is al in gebruik",invalidLogin:"Ongeldige naam of wachtwoord",signOut:"Uitloggen",weeksBooked:"weken geboekt",book:"Boeken",calendar:"Kalender",bookingPhase:"Boekingsfase",year:"Jaar",myBookings:"Mijn Boekingen",noBookingsYet:"Nog geen boekingen",familyBranches:"Stammen",clickBranch:"Klik op een stam om de leden te zien",all:"Alle",noWeeks:"Geen weken beschikbaar",bookBtn:"Boeken",cancelBtn:"Annuleren",yourBooking:"Jouw boeking",branchAlreadyBooked:"Je stam heeft al een week geboekt",youAlreadyBooked:"Je hebt al een week geboekt",alreadyBookedWeek:"Je hebt deze week al geboekt!",branchBookedPhase:"Je stam heeft al een week geboekt in deze fase",youBookedPhase:"Je hebt al een week geboekt in deze fase",booked:"Geboekt",bookingCancelled:"Boeking geannuleerd",available:"Beschikbaar",doubleBooking:"Dubbele boeking",noMembers:"Nog geen leden geregistreerd",membersRegistered:"leden geregistreerd",memberRegistered:"lid geregistreerd",close:"Sluiten",admin:"Beheerder",adminTitle:"Boeking namens iemand anders",adminDesc:"Voor eigenaren die per e-mail boeken",owner:"Eigenaar",pickOwner:"Kies een eigenaar...",phase:"Fase",week:"Week",pickWeek:"Kies een week...",clanName:"Stamboeking",clanDesc:"Elke stam kiest een week",clanRule:"Een week per stam",priorityName:"Prioriteitsboeking",priorityDesc:"Elke eigenaar kiest een week",priorityRule:"Een week per eigenaar",regularName:"Reguliere Boeking",regularDesc:"Boek zoveel als je wilt",regularRule:"Onbeperkt, wie het eerst komt",revealed:"Onthuld",hidden:"Verborgen tot onthulling",hiddenCalNote:"Stam- en prioriteitsboekingen zijn nog niet onthuld. Alleen je eigen en reguliere boekingen worden getoond.",adminBooking:"via beheerder",bookOnBehalf:"Namens iemand boeken",loading:"Laden...",emailTab:"Email plakken",manualTab:"Handmatig",pasteEmail:"Plak hier de email...",parseEmail:"Email lezen",parsing:"Email wordt gelezen...",parsedResult:"Uit email gehaald",noMatch:"Kon geen geregistreerde eigenaar vinden",weekNotFound:"Kon de gevraagde week niet herkennen",parseError:"Kon de email niet verwerken.",confirmBooking:"Boeking bevestigen",parsedName:"Herkende naam",parsedWeek:"Gevraagde week",feedback:"Feedback",surveyTitle:"Wat vind je ervan?",surveyIntro:"Help ons het boekingssysteem te verbeteren. Je antwoorden zijn anoniem.",q1:"Wat vind je over het algemeen van deze boekingstool?",q1a:"Geweldig",q1b:"Ziet er goed uit, maar kan beter",q1c:"Ik heb zorgen",q1d:"Ik geef de voorkeur aan het huidige e-mailsysteem",q2:"Zou je deze tool gebruiken om zelf je weken te boeken?",q2a:"Ja, zeker",q2b:"Ja, maar ik wil ook per e-mail kunnen boeken",q2c:"Nee, ik boek liever per e-mail",q3:"Welke functies zijn het belangrijkst voor jou?",q3a:"Zien welke weken beschikbaar zijn",q3b:"Zien wie welke week heeft geboekt (kalender)",q3c:"Kunnen boeken zonder Marielle te contacteren",q3d:"Nederlandse taalondersteuning",q3e:"Eenvoudig en makkelijk te gebruiken",q4:"Wil je iets veranderen of toevoegen?",q4placeholder:"Typ hier je suggesties...",q5:"Hoe comfortabel ben je met het gebruik van websites/apps?",q5a:"Zeer comfortabel",q5b:"Redelijk comfortabel",q5c:"Het moet heel eenvoudig zijn",q5d:"Ik gebruik liever geen website",submitFeedback:"Feedback versturen",feedbackThanks:"Bedankt voor je feedback!",feedbackSaved:"Je reactie is opgeslagen.",submitAnother:"Nog een reactie versturen",feedbackCount:"reacties verzameld",phaseOpen:"Open van",phaseTo:"tot",phaseRevealed:"Boekingen worden onthuld op",phaseVisibleNow:"Boekingen zijn direct zichtbaar",phaseOpensOn:"Opent op",phaseClosed:"Gesloten",phaseRevealedOn:"Boekingen werden onthuld op",
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
  return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
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
    weeks.push({ id: `${year}-W${String(weekNum).padStart(2,'0')}`, weekNum, start: toISO(start), end: toISO(end), month: start.getMonth() });
    d.setDate(d.getDate() + 7); weekNum++;
  }
  return weeks;
}

function formatDate(iso, lang) {
  if (!iso) return '';
  const p = iso.split('-');
  return `${parseInt(p[2])} ${MONTHS[lang][parseInt(p[1])-1]}`;
}

function formatFullDate(iso, lang) {
  if (!iso) return '';
  const p = iso.split('-');
  return `${parseInt(p[2])} ${MONTHS[lang][parseInt(p[1])-1]} ${p[0]}`;
}

// ═══════════════════════════════════════════════════════
// MOCK DATA
// ═══════════════════════════════════════════════════════
const MOCK_USERS = [
  { id: 1, name: "Marielle", branch_id: 1, is_admin: true },
  { id: 2, name: "Jean-Pierre Fromageot", branch_id: 1, is_admin: false },
  { id: 3, name: "Sophie Van der Grinten", branch_id: 2, is_admin: false },
  { id: 4, name: "Thomas Van der Grinten", branch_id: 2, is_admin: false },
  { id: 5, name: "Anna Stam3", branch_id: 3, is_admin: false },
  { id: 6, name: "Peter Stam4", branch_id: 4, is_admin: false },
];

const MOCK_PHASE_CONFIG = {
  year: 2026,
  clan_start: '2025-11-01', clan_end: '2025-12-11', clan_reveal: '2025-12-12',
  priority_start: '2025-12-12', priority_end: '2025-12-30', priority_reveal: '2025-12-31',
  regular_start: '2026-01-01',
};

const INITIAL_BOOKINGS = [
  { id: 101, week_id: '2026-W12', year: 2026, user_id: 3, branch_id: 2, phase: 'clan', admin_booked: false, user_name: 'Sophie Van der Grinten', branch_name: 'Van der Grinten' },
  { id: 102, week_id: '2026-W28', year: 2026, user_id: 2, branch_id: 1, phase: 'clan', admin_booked: false, user_name: 'Jean-Pierre Fromageot', branch_name: 'Fromageot' },
  { id: 103, week_id: '2026-W30', year: 2026, user_id: 3, branch_id: 2, phase: 'priority', admin_booked: false, user_name: 'Sophie Van der Grinten', branch_name: 'Van der Grinten' },
  { id: 104, week_id: '2026-W35', year: 2026, user_id: 5, branch_id: 3, phase: 'regular', admin_booked: false, user_name: 'Anna Stam3', branch_name: 'Stam 3' },
  { id: 105, week_id: '2026-W35', year: 2026, user_id: 6, branch_id: 4, phase: 'regular', admin_booked: true, user_name: 'Peter Stam4', branch_name: 'Stam 4' },
];

const MOCK_BRANCHES = FAMILY_BRANCHES.map(b => ({
  ...b,
  member_count: MOCK_USERS.filter(u => u.branch_id === b.id).length,
}));

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
  const today = new Date().toISOString().split('T')[0];
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
function Toast({ message }) {
  if (!message) return null;
  return React.createElement('div', { style: {
    position:'fixed',top:20,right:20,background:COLORS.text,color:COLORS.white,
    padding:'12px 24px',borderRadius:10,fontSize:14,fontWeight:500,zIndex:1000,
    boxShadow:'0 4px 20px rgba(0,0,0,0.15)',animation:'fadeIn 0.3s ease',fontFamily:"'DM Sans', sans-serif",
  }}, message);
}

// ═══════════════════════════════════════════════════════
// PHASE INFO BOX
// ═══════════════════════════════════════════════════════
function PhaseInfoBox({ phase, phaseConfig, t, lang }) {
  if (!phaseConfig) return null;
  const today = new Date().toISOString().split('T')[0];
  let startDate, endDate, revealDate, isActive, isRevealed;
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
  const pc = phaseColor(phase);

  if (!isActive && today < startDate) {
    return React.createElement('div', { style: { background:'#F5F0EB',border:'1px solid #E0D8CF',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#8B7D6B',marginTop:12,lineHeight:1.5 }},
      '\u{1F512} ', t.phaseOpensOn, ' ', React.createElement('strong',null,formatFullDate(startDate,lang))
    );
  }
  if (!isActive && today > (endDate || startDate)) {
    return React.createElement('div', { style: { background:'#F5F0EB',border:'1px solid #E0D8CF',borderRadius:10,padding:'10px 14px',fontSize:12,color:'#8B7D6B',marginTop:12,lineHeight:1.5 }},
      isRevealed && revealDate
        ? React.createElement(React.Fragment,null,'\u2705 ', t.phaseRevealedOn, ' ', React.createElement('strong',null,formatFullDate(revealDate,lang)))
        : React.createElement(React.Fragment,null,'\u{1F512} ', t.phaseClosed)
    );
  }
  return React.createElement('div', { style: { background:pc+'0A',border:`1px solid ${pc}30`,borderRadius:10,padding:'10px 14px',fontSize:12,color:'#5A4D40',marginTop:12,lineHeight:1.6 }},
    '\u{1F4C5} ', t.phaseOpen, ' ', React.createElement('strong',null,formatFullDate(startDate,lang)),
    endDate && React.createElement(React.Fragment,null,' ',t.phaseTo,' ',React.createElement('strong',null,formatFullDate(endDate,lang))),
    revealDate && React.createElement('div',{style:{marginTop:2}},'\u{1F50D} ',t.phaseRevealed,' ',React.createElement('strong',null,formatFullDate(revealDate,lang))),
    !revealDate && React.createElement('div',{style:{marginTop:2}},'\u2705 ',t.phaseVisibleNow),
  );
}

// ═══════════════════════════════════════════════════════
// BRANCH MEMBERS PANEL
// ═══════════════════════════════════════════════════════
function BranchMembersPanel({ branch, onClose, t }) {
  const members = MOCK_USERS.filter(u => u.branch_id === branch.id);
  return React.createElement('div', { style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'}, onClick:onClose },
    React.createElement('div', { onClick:e=>e.stopPropagation(), style:{background:COLORS.white,borderRadius:16,padding:28,width:360,maxHeight:'70vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'} },
      React.createElement('div', { style:{display:'flex',alignItems:'center',gap:10,marginBottom:20} },
        React.createElement('div', { style:{width:16,height:16,borderRadius:4,background:branch.color} }),
        React.createElement('h3', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display', Georgia, serif"} }, branch.name),
      ),
      members.length === 0
        ? React.createElement('p', { style:{fontSize:14,color:'#B0A090',margin:0} }, t.noMembers)
        : React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:8} },
            members.map(m =>
              React.createElement('div', { key:m.id, style:{display:'flex',alignItems:'center',gap:10,padding:'10px 14px',background:'#F8F5F2',borderRadius:10,borderLeft:`3px solid ${branch.color}`} },
                React.createElement('div', { style:{width:32,height:32,borderRadius:'50%',background:branch.color+'20',display:'flex',alignItems:'center',justifyContent:'center',fontSize:14,fontWeight:700,color:branch.color} }, m.name.charAt(0).toUpperCase()),
                React.createElement('div', null,
                  React.createElement('span', { style:{fontSize:14,fontWeight:500,color:COLORS.text} }, m.name),
                  m.is_admin && React.createElement('span', { style:{fontSize:10,background:'#C4853B20',color:'#C4853B',padding:'2px 6px',borderRadius:4,marginLeft:6,fontWeight:600} }, t.admin),
                ),
              )
            ),
          ),
      React.createElement('div', { style:{marginTop:16,fontSize:12,color:COLORS.muted} }, `${members.length} ${members.length!==1?t.membersRegistered:t.memberRegistered}`),
      React.createElement('button', { onClick:onClose, style:{marginTop:16,width:'100%',padding:'10px 0',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.close),
    )
  );
}

// ═══════════════════════════════════════════════════════
// ADMIN PANEL
// ═══════════════════════════════════════════════════════
function AdminPanel({ users, weeks, year, onBooked, onClose, t, lang }) {
  const [tab, setTab] = useState('manual');
  const [selectedUser, setSelectedUser] = useState('');
  const [selectedWeek, setSelectedWeek] = useState('');
  const [selectedPhase, setSelectedPhase] = useState('regular');

  const handleSubmit = () => {
    if (!selectedUser || !selectedWeek) return;
    const u = users.find(x => x.id === parseInt(selectedUser));
    if (u) onBooked(selectedWeek, u, selectedPhase);
    onClose();
  };

  return React.createElement('div', { style:{position:'fixed',inset:0,background:'rgba(0,0,0,0.3)',zIndex:200,display:'flex',alignItems:'center',justifyContent:'center'}, onClick:onClose },
    React.createElement('div', { onClick:e=>e.stopPropagation(), style:{background:COLORS.white,borderRadius:16,padding:28,width:480,maxHeight:'85vh',overflow:'auto',boxShadow:'0 12px 48px rgba(0,0,0,0.15)'} },
      React.createElement('h3', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:'0 0 6px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.adminTitle),
      React.createElement('p', { style:{fontSize:13,color:COLORS.muted,margin:'0 0 16px 0'} }, t.adminDesc),
      // Tabs
      React.createElement('div', { style:{display:'flex',gap:0,marginBottom:20,background:COLORS.bg,borderRadius:10,padding:3} },
        [{id:'email',label:'\u{1F4E7} '+t.emailTab},{id:'manual',label:'\u270F\uFE0F '+t.manualTab}].map(tb =>
          React.createElement('button', { key:tb.id, onClick:()=>setTab(tb.id), style:{flex:1,padding:'9px 0',border:'none',borderRadius:8,background:tab===tb.id?COLORS.white:'transparent',color:tab===tb.id?COLORS.text:COLORS.muted,fontWeight:tab===tb.id?600:400,cursor:'pointer',fontSize:13,boxShadow:tab===tb.id?'0 1px 4px rgba(0,0,0,0.08)':'none',fontFamily:"'DM Sans', sans-serif"} }, tb.label)
        )
      ),
      tab === 'email' && React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:14} },
        React.createElement('textarea', { placeholder:t.pasteEmail, rows:6, style:{padding:'12px 16px',border:`1.5px solid ${COLORS.border}`,borderRadius:10,fontSize:14,outline:'none',resize:'vertical',fontFamily:'inherit',lineHeight:1.5} }),
        React.createElement('div', { style:{padding:16,background:'#3B6B9E10',border:'1px solid #3B6B9E30',borderRadius:10,textAlign:'center',fontSize:13,color:'#3B6B9E'} }, '\u{1F916} Email parsing requires the backend API with Anthropic key. Use manual tab for this demo.'),
        React.createElement('button', { onClick:onClose, style:{padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.cancelBtn),
      ),
      tab === 'manual' && React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:14} },
        // Owner select
        React.createElement('div', null,
          React.createElement('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.owner),
          React.createElement('select', { value:selectedUser, onChange:e=>setSelectedUser(e.target.value), style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans', sans-serif"} },
            React.createElement('option', { value:'' }, t.pickOwner),
            users.map(u => { const br = FAMILY_BRANCHES.find(b=>b.id===u.branch_id); return React.createElement('option', { key:u.id, value:u.id }, `${u.name} (${br?br.name:'?'})`); })
          )
        ),
        // Phase select
        React.createElement('div', null,
          React.createElement('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.phase),
          React.createElement('div', { style:{display:'flex',gap:6} },
            PHASE_ORDER.map(pid => {
              const p = getPhaseMeta(pid,t);
              return React.createElement('button', { key:pid, onClick:()=>setSelectedPhase(pid), style:{flex:1,padding:'8px 0',border:selectedPhase===pid?'1.5px solid #B85042':`1.5px solid ${COLORS.border}`,borderRadius:8,background:selectedPhase===pid?'#B850420A':COLORS.white,fontSize:11,fontWeight:600,color:selectedPhase===pid?'#B85042':COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, `${p.icon} ${p.name}`);
            })
          )
        ),
        // Week select
        React.createElement('div', null,
          React.createElement('label', { style:{fontSize:12,fontWeight:600,color:'#5A4D40',display:'block',marginBottom:6} }, t.week),
          React.createElement('select', { value:selectedWeek, onChange:e=>setSelectedWeek(e.target.value), style:{width:'100%',padding:'10px 14px',border:'1.5px solid #E0D8CF',borderRadius:10,fontSize:14,outline:'none',background:'white',fontFamily:"'DM Sans', sans-serif"} },
            React.createElement('option', { value:'' }, t.pickWeek),
            weeks.map(w => React.createElement('option', { key:w.id, value:w.id }, `W${w.weekNum}: ${formatDate(w.start,lang)} – ${formatDate(w.end,lang)}`))
          )
        ),
        // Buttons
        React.createElement('div', { style:{display:'flex',gap:8,marginTop:4} },
          React.createElement('button', { onClick:onClose, style:{flex:1,padding:'11px 0',background:'#F5F0EB',border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.cancelBtn),
          React.createElement('button', { onClick:handleSubmit, disabled:!selectedUser||!selectedWeek, style:{flex:1,padding:'11px 0',background:(!selectedUser||!selectedWeek)?'#D4C5B5':COLORS.success,border:'none',borderRadius:10,fontSize:14,fontWeight:600,color:'white',cursor:(!selectedUser||!selectedWeek)?'default':'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.bookBtn),
        ),
      ),
    )
  );
}

// ═══════════════════════════════════════════════════════
// WEEK ROW
// ═══════════════════════════════════════════════════════
function WeekRow({ week, myBooking, onBook, onCancel, user, phase, clanBookedByBranch, userBookedPriority, phaseActive, t, lang }) {
  const hasMyBooking = !!myBooking;
  const userBranch = FAMILY_BRANCHES.find(b => b.id === user.branch_id);
  let bookable = false, reason = '';
  if (!hasMyBooking && phaseActive) {
    if (phase === 'clan') { if (clanBookedByBranch) reason = t.branchAlreadyBooked; else bookable = true; }
    else if (phase === 'priority') { if (userBookedPriority) reason = t.youAlreadyBooked; else bookable = true; }
    else bookable = true;
  }
  return React.createElement('div', { style:{display:'flex',alignItems:'center',padding:'10px 16px',borderRadius:10,background:hasMyBooking?userBranch.color+'0D':'transparent',transition:'all 0.2s',gap:12,borderLeft:hasMyBooking?`3px solid ${userBranch.color}`:'3px solid transparent'} },
    React.createElement('div', { style:{width:36,textAlign:'center',fontSize:13,fontWeight:600,color:COLORS.muted} }, `W${week.weekNum}`),
    React.createElement('div', { style:{flex:1,minWidth:0} },
      React.createElement('div', { style:{fontSize:14,fontWeight:500,color:COLORS.text} }, `${formatDate(week.start,lang)} — ${formatDate(week.end,lang)}`),
      hasMyBooking && React.createElement('div', { style:{fontSize:12,color:userBranch.color,fontWeight:600,marginTop:2} }, `${t.yourBooking} · ${getPhaseMeta(myBooking.phase,t).name}${myBooking.admin_booked?' · '+t.adminBooking:''}`),
      !hasMyBooking && reason && React.createElement('div', { style:{fontSize:11,color:'#B0A090',marginTop:2} }, reason),
    ),
    React.createElement('div', null,
      hasMyBooking && React.createElement('button', { onClick:()=>onCancel(myBooking), style:{padding:'6px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans', sans-serif"} }, t.cancelBtn),
      !hasMyBooking && bookable && React.createElement('button', { onClick:()=>onBook(week), style:{padding:'6px 14px',background:COLORS.success,border:'none',borderRadius:8,fontSize:12,color:COLORS.white,cursor:'pointer',fontWeight:600,fontFamily:"'DM Sans', sans-serif"} }, t.bookBtn),
    ),
  );
}

// ═══════════════════════════════════════════════════════
// BOOKING VIEW
// ═══════════════════════════════════════════════════════
function BookingView({ user, bookings, weeks, year, setYear, phase, setPhase, phaseConfig, filterMonth, setFilterMonth, branches, onSelectBranch, onShowAdmin, onBook, onCancel, showToast, t, lang }) {
  const myBookings = bookings.filter(b => b.user_id === user.id);
  const myBookingMap = {}; myBookings.forEach(b => { myBookingMap[b.week_id] = b; });
  const clanBookedByBranch = bookings.find(b => b.branch_id === user.branch_id && b.phase === 'clan');
  const userBookedPriority = bookings.find(b => b.user_id === user.id && b.phase === 'priority');
  const filteredWeeks = filterMonth !== null ? weeks.filter(w => w.month === filterMonth) : weeks;
  const userBranch = FAMILY_BRANCHES.find(b => b.id === user.branch_id);

  const handleBook = (week) => onBook(week, phase);
  const handleCancel = (booking) => onCancel(booking);

  return React.createElement('div', { style:{display:'flex',maxWidth:1200,margin:'0 auto',gap:24,padding:'24px 20px'} },
    // SIDEBAR
    React.createElement('div', { style:{width:280,flexShrink:0,display:'flex',flexDirection:'column',gap:16} },
      // Phase selector
      React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        React.createElement('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.bookingPhase),
        React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:6} },
          PHASE_ORDER.map(pid => {
            const p = getPhaseMeta(pid,t); const active = isPhaseActive(pid,phaseConfig);
            return React.createElement('button', { key:pid, onClick:()=>setPhase(pid), style:{display:'flex',alignItems:'flex-start',gap:10,padding:'12px 14px',border:phase===pid?'1.5px solid #B85042':'1.5px solid transparent',borderRadius:10,background:phase===pid?'#B850420A':'#F8F5F2',cursor:'pointer',textAlign:'left',opacity:active?1:0.5} },
              React.createElement('span', { style:{fontSize:18} }, p.icon),
              React.createElement('div', null,
                React.createElement('div', { style:{fontSize:13,fontWeight:600,color:phase===pid?'#B85042':COLORS.text} }, p.name),
                React.createElement('div', { style:{fontSize:11,color:COLORS.muted,marginTop:2} }, p.rule),
              ),
            );
          })
        ),
        React.createElement(PhaseInfoBox, { phase, phaseConfig, t, lang }),
      ),
      // Year
      React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        React.createElement('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.year),
        React.createElement('div', { style:{display:'flex',gap:8} },
          [2026,2027].map(y => React.createElement('button', { key:y, onClick:()=>setYear(y), style:{flex:1,padding:'10px 0',border:year===y?`1.5px solid ${COLORS.success}`:`1.5px solid ${COLORS.border}`,borderRadius:8,background:year===y?COLORS.success+'0D':COLORS.white,color:year===y?COLORS.success:COLORS.muted,fontWeight:600,fontSize:14,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, y))
        ),
      ),
      // My bookings
      React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        React.createElement('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, `${t.myBookings} (${myBookings.length})`),
        myBookings.length === 0
          ? React.createElement('p', { style:{fontSize:13,color:'#B0A090',margin:0} }, t.noBookingsYet)
          : React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:6} },
              myBookings.map(b => { const w = weeks.find(w=>w.id===b.week_id); if(!w) return null;
                return React.createElement('div', { key:b.id, style:{padding:'8px 12px',background:'#F8F5F2',borderRadius:8,borderLeft:`3px solid ${userBranch.color}`} },
                  React.createElement('div', { style:{fontSize:13,fontWeight:500,color:COLORS.text} }, `${formatDate(w.start,lang)} — ${formatDate(w.end,lang)}`),
                  React.createElement('div', { style:{fontSize:11,color:COLORS.muted,marginTop:2} }, `W${w.weekNum} · ${getPhaseMeta(b.phase,t).name}${b.admin_booked?' · '+t.adminBooking:''}`),
                );
              })
            ),
      ),
      // Branches
      React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        React.createElement('h3', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.familyBranches),
        React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:4} },
          FAMILY_BRANCHES.map(b => {
            const bd = branches.find(br=>br.id===b.id); const mc = bd?.member_count||0;
            return React.createElement('button', { key:b.id, onClick:()=>onSelectBranch(b), style:{display:'flex',alignItems:'center',gap:8,fontSize:13,padding:'8px 10px',borderRadius:8,border:'none',background:'transparent',cursor:'pointer',width:'100%',textAlign:'left',fontFamily:"'DM Sans', sans-serif"} },
              React.createElement('div', { style:{width:10,height:10,borderRadius:3,background:b.color,flexShrink:0} }),
              React.createElement('span', { style:{color:COLORS.text,flex:1} }, b.name),
              React.createElement('span', { style:{fontSize:11,color:'#B0A090'} }, `${mc}\u{1F464}`),
            );
          })
        ),
        React.createElement('p', { style:{fontSize:11,color:'#B0A090',margin:'10px 0 0 0'} }, t.clickBranch),
      ),
      // Admin button
      user.is_admin && React.createElement('button', { onClick:onShowAdmin, style:{padding:'12px 16px',background:'#C4853B10',border:'1.5px solid #C4853B30',borderRadius:12,fontSize:13,fontWeight:600,color:'#C4853B',cursor:'pointer',display:'flex',alignItems:'center',gap:8,justifyContent:'center',fontFamily:"'DM Sans', sans-serif"} }, `\u{1F511} ${t.bookOnBehalf}`),
    ),
    // MAIN
    React.createElement('div', { style:{flex:1,minWidth:0} },
      // Month filter
      React.createElement('div', { style:{display:'flex',gap:4,marginBottom:16,flexWrap:'wrap'} },
        React.createElement('button', { onClick:()=>setFilterMonth(null), style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===null?COLORS.text:'#E8DFD5',color:filterMonth===null?COLORS.white:COLORS.muted,fontSize:12,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.all),
        MONTHS[lang].map((m,i) => { if (!weeks.some(w=>w.month===i)) return null;
          return React.createElement('button', { key:m, onClick:()=>setFilterMonth(i), style:{padding:'6px 12px',borderRadius:8,border:'none',background:filterMonth===i?COLORS.text:'#E8DFD5',color:filterMonth===i?COLORS.white:COLORS.muted,fontSize:12,fontWeight:500,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, m);
        }),
      ),
      // Phase banner
      (() => { const p = getPhaseMeta(phase,t); const pc = phaseColor(phase);
        return React.createElement('div', { style:{background:`linear-gradient(135deg, ${pc}15, ${pc}08)`,border:`1px solid ${pc}30`,borderRadius:12,padding:'14px 20px',marginBottom:16,display:'flex',alignItems:'center',gap:12} },
          React.createElement('span', { style:{fontSize:24} }, p.icon),
          React.createElement('div', null,
            React.createElement('div', { style:{fontSize:15,fontWeight:700,color:COLORS.text} }, p.name),
            React.createElement('div', { style:{fontSize:12,color:'#5A4D40'} }, `${p.desc} · ${p.rule}`),
          ),
        );
      })(),
      // Week list
      React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:8,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
        filteredWeeks.length === 0
          ? React.createElement('div', { style:{padding:40,textAlign:'center',color:COLORS.muted} }, t.noWeeks)
          : filteredWeeks.reduce((acc, week, i) => {
              const prevMonth = i > 0 ? filteredWeeks[i-1].month : -1;
              if (week.month !== prevMonth) acc.push(React.createElement('div', { key:`month-${week.month}`, style:{padding:'14px 16px 6px 16px',fontSize:13,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'0.5px',borderTop:i>0?'1px solid #F0EBE5':'none',marginTop:i>0?4:0} }, `${MONTHS[lang][week.month]} ${year}`));
              acc.push(React.createElement(WeekRow, { key:week.id, week, myBooking:myBookingMap[week.id], onBook:handleBook, onCancel:handleCancel, user, phase, clanBookedByBranch, userBookedPriority, phaseActive:isPhaseActive(phase,phaseConfig)||user.is_admin, t, lang }));
              return acc;
            }, [])
      ),
    ),
  );
}

// ═══════════════════════════════════════════════════════
// CALENDAR VIEW
// ═══════════════════════════════════════════════════════
function CalendarView({ user, bookings, weeks, year, setYear, phaseConfig, branches, onSelectBranch, t, lang }) {
  const today = new Date().toISOString().split('T')[0];
  const clanRevealed = phaseConfig ? today >= phaseConfig.clan_reveal : false;
  const priorityRevealed = phaseConfig ? today >= phaseConfig.priority_reveal : false;
  const visibleBookings = bookings.filter(b => { if(b.phase==='regular') return true; if(b.user_id===user.id) return true; if(user.is_admin) return true; if(b.phase==='clan') return clanRevealed; if(b.phase==='priority') return priorityRevealed; return false; });
  const someHidden = !clanRevealed || !priorityRevealed;
  const monthGroups = {}; weeks.forEach(w => { if(!monthGroups[w.month]) monthGroups[w.month]=[]; monthGroups[w.month].push(w); });

  return React.createElement('div', { style:{maxWidth:900,margin:'0 auto',padding:'24px 20px'} },
    React.createElement('div', { style:{display:'flex',gap:8,marginBottom:16,alignItems:'center',flexWrap:'wrap'} },
      [2026,2027].map(y => React.createElement('button', { key:y, onClick:()=>setYear(y), style:{padding:'8px 20px',border:year===y?`1.5px solid ${COLORS.success}`:`1.5px solid ${COLORS.border}`,borderRadius:8,background:year===y?COLORS.success+'0D':COLORS.white,color:year===y?COLORS.success:COLORS.muted,fontWeight:600,fontSize:14,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, y))
    ),
    someHidden && React.createElement('div', { style:{background:'#C4853B10',border:'1px solid #C4853B30',borderRadius:12,padding:'12px 18px',fontSize:13,color:'#8B6030',display:'flex',alignItems:'center',gap:8,marginBottom:16} }, `\u{1F512} ${t.hiddenCalNote}`),
    React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:16} },
      Object.entries(monthGroups).map(([monthIdx, monthWeeks]) => {
        const mi = parseInt(monthIdx);
        return React.createElement('div', { key:mi, style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
          React.createElement('h3', { style:{fontSize:15,fontWeight:700,color:COLORS.text,margin:'0 0 14px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, `${FULL_MONTHS[lang][mi]} ${year}`),
          React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:6} },
            monthWeeks.map(week => {
              const wb = visibleBookings.filter(b=>b.week_id===week.id);
              const fb = wb.length===1 ? FAMILY_BRANCHES.find(b=>b.id===wb[0].branch_id) : null;
              return React.createElement('div', { key:week.id, style:{display:'flex',alignItems:'stretch',borderRadius:8,overflow:'hidden',background:'#F8F5F2',minHeight:44} },
                React.createElement('div', { style:{width:56,display:'flex',alignItems:'center',justifyContent:'center',fontSize:12,fontWeight:600,color:COLORS.muted,background:'#F0EBE5',flexShrink:0} }, `W${week.weekNum}`),
                React.createElement('div', { style:{width:140,display:'flex',alignItems:'center',padding:'0 12px',fontSize:13,color:'#5A4D40',flexShrink:0} }, `${formatDate(week.start,lang)} – ${formatDate(week.end,lang)}`),
                React.createElement('div', { style:{flex:1,display:'flex',flexDirection:'column',justifyContent:'center',padding:'6px 12px',gap:4,borderLeft:wb.length>0?`3px solid ${wb.length>1?COLORS.warning:fb?.color||COLORS.muted}`:'3px solid transparent',background:wb.length>1?COLORS.warning+'10':wb.length===1?(fb?.color||'#ccc')+'15':'transparent'} },
                  wb.length===0 && React.createElement('span', { style:{fontSize:12,color:'#C8BDB0'} }, t.available),
                  wb.length>1 && React.createElement('div', { style:{fontSize:10,fontWeight:700,color:COLORS.warning,textTransform:'uppercase',letterSpacing:'0.5px'} }, `\u26A0 ${t.doubleBooking} (${wb.length})`),
                  wb.map(booking => {
                    const family = FAMILY_BRANCHES.find(b=>b.id===booking.branch_id);
                    const isOwn = booking.user_id === user.id;
                    return React.createElement('div', { key:booking.id, style:{display:'flex',alignItems:'center',gap:6,flexWrap:'wrap'} },
                      React.createElement('div', { style:{width:8,height:8,borderRadius:2,background:family?.color||'#ccc',flexShrink:0} }),
                      React.createElement('span', { style:{fontSize:13,fontWeight:600,color:family?.color||'#333'} }, `${booking.user_name}${isOwn?' \u2713':''}`),
                      React.createElement('span', { style:{fontSize:11,color:COLORS.muted} }, family?.name||''),
                      React.createElement('span', { style:{fontSize:10,color:'#B0A090',textTransform:'uppercase',letterSpacing:'0.5px'} }, getPhaseName(booking.phase,t)),
                      booking.admin_booked && React.createElement('span', { style:{fontSize:9,color:COLORS.warning,fontWeight:600} }, `(${t.adminBooking})`),
                    );
                  }),
                ),
              );
            })
          ),
        );
      })
    ),
    // Branch legend
    React.createElement('div', { style:{background:COLORS.white,borderRadius:14,padding:20,boxShadow:'0 1px 8px rgba(0,0,0,0.04)',marginTop:16} },
      React.createElement('h4', { style:{fontSize:11,fontWeight:700,color:COLORS.muted,textTransform:'uppercase',letterSpacing:'1px',margin:'0 0 12px 0'} }, t.familyBranches),
      React.createElement('div', { style:{display:'flex',flexWrap:'wrap',gap:8} },
        FAMILY_BRANCHES.map(b => { const bd = branches.find(br=>br.id===b.id); const mc = bd?.member_count||0;
          return React.createElement('button', { key:b.id, onClick:()=>onSelectBranch(b), style:{display:'flex',alignItems:'center',gap:6,fontSize:13,padding:'6px 12px',borderRadius:8,border:'1.5px solid #E8DFD5',background:COLORS.white,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} },
            React.createElement('div', { style:{width:10,height:10,borderRadius:3,background:b.color} }),
            React.createElement('span', { style:{color:COLORS.text} }, b.name),
            React.createElement('span', { style:{fontSize:11,color:COLORS.muted} }, `${mc}\u{1F464}`),
          );
        })
      ),
    ),
  );
}

// ═══════════════════════════════════════════════════════
// FEEDBACK VIEW
// ═══════════════════════════════════════════════════════
function FeedbackView({ t, lang, user }) {
  const [q1,setQ1]=useState(''); const [q2,setQ2]=useState(''); const [q3,setQ3]=useState([]); const [q4,setQ4]=useState(''); const [q5,setQ5]=useState('');
  const [submitted,setSubmitted]=useState(false); const [feedbackCount]=useState(3);
  const toggleQ3 = v => setQ3(p => p.includes(v)?p.filter(x=>x!==v):[...p,v]);

  const radioStyle = sel => ({padding:'10px 16px',border:sel?`2px solid ${COLORS.success}`:'2px solid #E8DFD5',borderRadius:10,background:sel?COLORS.success+'0D':COLORS.white,cursor:'pointer',fontSize:14,fontWeight:sel?600:400,color:sel?COLORS.success:COLORS.text,textAlign:'left',width:'100%',transition:'all 0.15s',fontFamily:"'DM Sans', sans-serif"});
  const checkStyle = sel => ({padding:'10px 16px',border:sel?'2px solid #3B6B9E':'2px solid #E8DFD5',borderRadius:10,background:sel?'#3B6B9E0D':COLORS.white,cursor:'pointer',fontSize:14,fontWeight:sel?600:400,color:sel?'#3B6B9E':COLORS.text,textAlign:'left',width:'100%',transition:'all 0.15s',fontFamily:"'DM Sans', sans-serif"});
  const qStyle = {fontSize:15,fontWeight:600,color:'#2C1810',marginBottom:10};

  if (submitted) return React.createElement('div', { style:{maxWidth:600,margin:'0 auto',padding:'40px 20px',textAlign:'center'} },
    React.createElement('div', { style:{background:COLORS.white,borderRadius:16,padding:40,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
      React.createElement('div', { style:{fontSize:48,marginBottom:16} }, '\u{1F389}'),
      React.createElement('h2', { style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 8px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.feedbackThanks),
      React.createElement('p', { style:{fontSize:14,color:COLORS.muted,margin:'0 0 24px 0'} }, t.feedbackSaved),
      React.createElement('button', { onClick:()=>{setSubmitted(false);setQ1('');setQ2('');setQ3([]);setQ4('');setQ5('');}, style:{padding:'12px 28px',background:COLORS.bg,border:'none',borderRadius:10,fontSize:14,fontWeight:500,color:'#5A4D40',cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, t.submitAnother),
    ));

  return React.createElement('div', { style:{maxWidth:600,margin:'0 auto',padding:'24px 20px'} },
    React.createElement('div', { style:{background:COLORS.white,borderRadius:16,padding:32,boxShadow:'0 1px 8px rgba(0,0,0,0.04)'} },
      React.createElement('div', { style:{textAlign:'center',marginBottom:28} },
        React.createElement('div', { style:{fontSize:32,marginBottom:8} }, '\u{1F4DD}'),
        React.createElement('h2', { style:{fontSize:22,fontWeight:700,color:COLORS.text,margin:'0 0 8px 0',fontFamily:"'Playfair Display', Georgia, serif"} }, t.surveyTitle),
        React.createElement('p', { style:{fontSize:14,color:COLORS.muted,margin:0,lineHeight:1.5} }, t.surveyIntro),
      ),
      React.createElement('div', { style:{display:'flex',flexDirection:'column',gap:28} },
        React.createElement('div',null,React.createElement('div',{style:qStyle},`1. ${t.q1}`),React.createElement('div',{style:{display:'flex',flexDirection:'column',gap:6}},[['a',t.q1a],['b',t.q1b],['c',t.q1c],['d',t.q1d]].map(([v,l])=>React.createElement('button',{key:v,onClick:()=>setQ1(v),style:radioStyle(q1===v)},`${q1===v?'\u25C9':'\u25CB'} ${l}`)))),
        React.createElement('div',null,React.createElement('div',{style:qStyle},`2. ${t.q2}`),React.createElement('div',{style:{display:'flex',flexDirection:'column',gap:6}},[['a',t.q2a],['b',t.q2b],['c',t.q2c]].map(([v,l])=>React.createElement('button',{key:v,onClick:()=>setQ2(v),style:radioStyle(q2===v)},`${q2===v?'\u25C9':'\u25CB'} ${l}`)))),
        React.createElement('div',null,React.createElement('div',{style:qStyle},`3. ${t.q3}`),React.createElement('div',{style:{fontSize:12,color:COLORS.muted,marginBottom:10}},'(multiple answers)'),React.createElement('div',{style:{display:'flex',flexDirection:'column',gap:6}},[['a',t.q3a],['b',t.q3b],['c',t.q3c],['d',t.q3d],['e',t.q3e]].map(([v,l])=>React.createElement('button',{key:v,onClick:()=>toggleQ3(v),style:checkStyle(q3.includes(v))},`${q3.includes(v)?'\u2611':'\u2610'} ${l}`)))),
        React.createElement('div',null,React.createElement('div',{style:qStyle},`4. ${t.q4}`),React.createElement('textarea',{value:q4,onChange:e=>setQ4(e.target.value),placeholder:t.q4placeholder,rows:4,style:{width:'100%',padding:'12px 16px',border:`1.5px solid ${COLORS.border}`,borderRadius:10,fontSize:14,outline:'none',resize:'vertical',fontFamily:'inherit',lineHeight:1.5,boxSizing:'border-box'}})),
        React.createElement('div',null,React.createElement('div',{style:qStyle},`5. ${t.q5}`),React.createElement('div',{style:{display:'flex',flexDirection:'column',gap:6}},[['a',t.q5a],['b',t.q5b],['c',t.q5c],['d',t.q5d]].map(([v,l])=>React.createElement('button',{key:v,onClick:()=>setQ5(v),style:radioStyle(q5===v)},`${q5===v?'\u25C9':'\u25CB'} ${l}`)))),
        React.createElement('button',{onClick:()=>setSubmitted(true),disabled:!q1||!q2||!q5,style:{padding:'14px 0',background:(!q1||!q2||!q5)?'#D4C5B5':COLORS.success,border:'none',borderRadius:12,fontSize:15,fontWeight:600,color:COLORS.white,cursor:(!q1||!q2||!q5)?'default':'pointer',marginTop:8,fontFamily:"'DM Sans', sans-serif"}},t.submitFeedback),
      ),
    ),
    feedbackCount>0 && React.createElement('div',{style:{textAlign:'center',marginTop:16,fontSize:12,color:'#B0A090'}},`${feedbackCount} ${t.feedbackCount}`),
  );
}

// ═══════════════════════════════════════════════════════
// LOGIN SCREEN
// ═══════════════════════════════════════════════════════
function LoginScreen({ t, lang, onLangToggle, onLogin }) {
  const [mode,setMode]=useState('login'); const [name,setName]=useState(''); const [password,setPassword]=useState(''); const [branchId,setBranchId]=useState(''); const [error,setError]=useState('');
  const handleSubmit = e => {
    e.preventDefault(); setError('');
    if (!name.trim()||!password.trim()) return setError(t.fillAll);
    if (mode==='register'&&!branchId) return setError(t.pickBranch);
    // Demo: any login works. Admin if name contains "marielle"
    const isAdmin = name.toLowerCase().includes('marielle');
    const bid = mode==='register' ? parseInt(branchId) : 1;
    onLogin({ id: Date.now(), name: name.trim(), branch_id: bid, is_admin: isAdmin });
  };
  const s = { input:{width:'100%',padding:'12px 16px',border:`1px solid ${COLORS.border}`,borderRadius:8,fontSize:14,fontFamily:"'DM Sans', sans-serif",color:COLORS.text,outline:'none',marginBottom:12,boxSizing:'border-box'} };
  return React.createElement('div', { style:{minHeight:'100vh',display:'flex',alignItems:'center',justifyContent:'center',background:COLORS.bg,fontFamily:"'DM Sans', sans-serif",padding:20} },
    React.createElement('div', { style:{background:COLORS.white,borderRadius:16,padding:'48px 40px',maxWidth:420,width:'100%',boxShadow:'0 4px 24px rgba(44,24,16,0.08)',position:'relative'} },
      React.createElement('button', { onClick:onLangToggle, style:{position:'absolute',top:16,right:16,background:'none',border:`1px solid ${COLORS.border}`,borderRadius:8,padding:'4px 12px',cursor:'pointer',fontSize:13,color:COLORS.muted,fontFamily:"'DM Sans', sans-serif"} }, lang==='en'?'NL':'EN'),
      React.createElement('h1', { style:{fontFamily:"'Playfair Display', serif",fontSize:32,fontWeight:700,color:COLORS.text,margin:'0 0 4px 0',textAlign:'center'} }, t.appName),
      React.createElement('p', { style:{fontSize:14,color:COLORS.muted,margin:'0 0 32px 0',textAlign:'center'} }, t.subtitle),
      React.createElement('div', { style:{display:'flex',gap:0,marginBottom:24,borderBottom:`2px solid ${COLORS.border}`} },
        ['login','register'].map(m => React.createElement('button', { key:m, onClick:()=>{setMode(m);setError('');}, style:{flex:1,padding:'10px 16px',background:'none',border:'none',borderBottom:'2px solid '+(mode===m?COLORS.accent:'transparent'),marginBottom:-2,cursor:'pointer',fontSize:14,fontWeight:600,color:mode===m?COLORS.text:COLORS.muted,fontFamily:"'DM Sans', sans-serif"} }, m==='login'?t.signIn:t.register))
      ),
      React.createElement('form', { onSubmit:handleSubmit },
        error && React.createElement('div', { style:{background:'#FEF2F2',color:COLORS.danger,padding:'10px 14px',borderRadius:8,fontSize:13,marginBottom:12} }, error),
        React.createElement('input', { style:s.input, type:'text', placeholder:t.fullName, value:name, onChange:e=>setName(e.target.value) }),
        React.createElement('input', { style:s.input, type:'password', placeholder:t.password, value:password, onChange:e=>setPassword(e.target.value) }),
        mode==='register' && React.createElement(React.Fragment, null,
          React.createElement('label', { style:{display:'block',fontSize:13,fontWeight:600,color:COLORS.muted,marginBottom:6} }, t.whichBranch),
          React.createElement('select', { style:{...s.input,cursor:'pointer',background:COLORS.white}, value:branchId, onChange:e=>setBranchId(e.target.value) },
            React.createElement('option', { value:'' }, t.pickBranch),
            FAMILY_BRANCHES.map(b => React.createElement('option', { key:b.id, value:b.id }, b.name))
          ),
        ),
        React.createElement('button', { type:'submit', style:{width:'100%',padding:12,background:COLORS.accent,color:COLORS.white,border:'none',borderRadius:8,fontSize:15,fontWeight:600,cursor:'pointer',fontFamily:"'DM Sans', sans-serif",marginTop:8} }, mode==='login'?t.signIn:t.createAccount),
        React.createElement('p', { style:{fontSize:11,color:'#B0A090',textAlign:'center',marginTop:16} }, 'Demo: Enter any name/password. Include "Marielle" for admin access.'),
      ),
    )
  );
}

// ═══════════════════════════════════════════════════════
// MAIN APP
// ═══════════════════════════════════════════════════════
function App() {
  const [lang, setLang] = useState(localStorage.getItem('fargny_lang')||'nl');
  const [user, setUser] = useState(null);
  const [view, setView] = useState('book');
  const [year, setYear] = useState(2026);
  const [phase, setPhase] = useState('regular');
  const [bookings, setBookings] = useState(INITIAL_BOOKINGS);
  const [filterMonth, setFilterMonth] = useState(null);
  const [toast, setToast] = useState(null);
  const [selectedBranch, setSelectedBranch] = useState(null);
  const [showAdminPanel, setShowAdminPanel] = useState(false);

  const t = T[lang] || T.en;
  const weeks = generateWeeks(year);
  const phaseConfig = MOCK_PHASE_CONFIG;

  const toggleLang = () => { const next = lang==='en'?'nl':'en'; setLang(next); localStorage.setItem('fargny_lang',next); };
  const showToast = msg => { setToast(msg); setTimeout(()=>setToast(null),2500); };
  const logout = () => setUser(null);

  const handleBook = (week, bookingPhase) => {
    const newBooking = { id: Date.now(), week_id: week.id, year, user_id: user.id, branch_id: user.branch_id, phase: bookingPhase, admin_booked: false, user_name: user.name, branch_name: FAMILY_BRANCHES.find(b=>b.id===user.branch_id)?.name||'' };
    setBookings(prev => [...prev, newBooking]);
    showToast(`${t.booked}: ${formatDate(week.start,lang)} — ${formatDate(week.end,lang)}`);
  };

  const handleCancel = booking => {
    setBookings(prev => prev.filter(b => b.id !== booking.id));
    showToast(t.bookingCancelled);
  };

  const handleAdminBook = (weekId, targetUser, bookingPhase) => {
    const w = weeks.find(w=>w.id===weekId);
    const newBooking = { id: Date.now(), week_id: weekId, year, user_id: targetUser.id, branch_id: targetUser.branch_id, phase: bookingPhase, admin_booked: true, user_name: targetUser.name, branch_name: FAMILY_BRANCHES.find(b=>b.id===targetUser.branch_id)?.name||'' };
    setBookings(prev => [...prev, newBooking]);
    showToast(`${t.booked}: ${targetUser.name} - ${w?formatDate(w.start,lang):weekId}`);
  };

  if (!user) return React.createElement(LoginScreen, { t, lang, onLangToggle: toggleLang, onLogin: setUser });

  const userBranch = FAMILY_BRANCHES.find(b => b.id === user.branch_id) || FAMILY_BRANCHES[0];
  const totalBooked = bookings.filter(b=>b.year===year).length;

  return React.createElement('div', { style:{minHeight:'100vh',background:COLORS.bg,fontFamily:"'DM Sans', 'Segoe UI', sans-serif"} },
    selectedBranch && React.createElement(BranchMembersPanel, { branch:selectedBranch, onClose:()=>setSelectedBranch(null), t }),
    showAdminPanel && React.createElement(AdminPanel, { users:MOCK_USERS, weeks, year, onBooked:handleAdminBook, onClose:()=>setShowAdminPanel(false), t, lang }),
    React.createElement(Toast, { message:toast }),

    // HEADER
    React.createElement('div', { style:{background:COLORS.white,borderBottom:`1px solid ${COLORS.border}`,padding:'12px 20px',display:'flex',alignItems:'center',justifyContent:'space-between',position:'sticky',top:0,zIndex:100,flexWrap:'wrap',gap:8} },
      React.createElement('div', { style:{display:'flex',alignItems:'center',gap:10} },
        React.createElement('span', { style:{fontSize:22} }, '\u{1F3E1}'),
        React.createElement('div', null,
          React.createElement('h1', { style:{fontSize:18,fontWeight:700,color:COLORS.text,margin:0,fontFamily:"'Playfair Display', Georgia, serif"} }, 'Fargny'),
          React.createElement('p', { style:{fontSize:11,color:COLORS.muted,margin:0} }, `${year} · ${totalBooked} ${t.weeksBooked}`),
        ),
      ),
      React.createElement('div', { style:{display:'flex',gap:0,background:COLORS.bg,borderRadius:10,padding:3} },
        [{id:'book',label:t.book,emoji:'\u{1F4DD}'},{id:'calendar',label:t.calendar,emoji:'\u{1F4C5}'},{id:'feedback',label:t.feedback,emoji:'\u{1F4CB}'}].map(v =>
          React.createElement('button', { key:v.id, onClick:()=>setView(v.id), style:{padding:'7px 16px',border:'none',borderRadius:8,background:view===v.id?COLORS.white:'transparent',color:view===v.id?COLORS.text:COLORS.muted,fontWeight:view===v.id?600:400,cursor:'pointer',fontSize:13,fontFamily:"'DM Sans', sans-serif",boxShadow:view===v.id?'0 1px 4px rgba(0,0,0,0.08)':'none'} }, `${v.emoji} ${v.label}`)
        )
      ),
      React.createElement('div', { style:{display:'flex',alignItems:'center',gap:10} },
        React.createElement('button', { onClick:toggleLang, style:{padding:'6px 12px',border:`1.5px solid ${COLORS.border}`,borderRadius:8,background:COLORS.white,fontSize:12,fontWeight:600,color:COLORS.muted,cursor:'pointer',fontFamily:"'DM Sans', sans-serif"} }, lang==='en'?'\u{1F1F3}\u{1F1F1} NL':'\u{1F1EC}\u{1F1E7} EN'),
        React.createElement('div', { style:{textAlign:'right'} },
          React.createElement('div', { style:{fontSize:13,fontWeight:600,color:COLORS.text} }, user.name),
          React.createElement('div', { style:{fontSize:11,color:userBranch.color,fontWeight:600} }, userBranch.name),
        ),
        React.createElement('button', { onClick:logout, style:{padding:'7px 14px',background:'transparent',border:'1.5px solid #D4C5B5',borderRadius:8,fontSize:12,color:COLORS.muted,cursor:'pointer',fontWeight:500,fontFamily:"'DM Sans', sans-serif"} }, t.signOut),
      ),
    ),

    // VIEWS
    view === 'book' && React.createElement(BookingView, { user, bookings:bookings.filter(b=>b.year===year), weeks, year, setYear:y=>{setYear(y);setFilterMonth(null);}, phase, setPhase, phaseConfig, filterMonth, setFilterMonth, branches:MOCK_BRANCHES, onSelectBranch:setSelectedBranch, onShowAdmin:()=>setShowAdminPanel(true), onBook:handleBook, onCancel:handleCancel, showToast, t, lang }),
    view === 'calendar' && React.createElement(CalendarView, { user, bookings:bookings.filter(b=>b.year===year), weeks, year, setYear, phaseConfig, branches:MOCK_BRANCHES, onSelectBranch:setSelectedBranch, t, lang }),
    view === 'feedback' && React.createElement(FeedbackView, { t, lang, user }),
  );
}

// ═══════════════════════════════════════════════════════
// MOUNT
// ═══════════════════════════════════════════════════════
const root = ReactDOM.createRoot(document.getElementById('fargny-booking-root'));
root.render(React.createElement(App));
