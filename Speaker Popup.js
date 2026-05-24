 // ══════════════════════════════
  // SPEAKER DATA
  // ══════════════════════════════
  const speakers = [
    {
      id: 'craig',
      img: 'Craig_Groeschel.jpg',
      name: 'Craig Groeschel',
      role: 'Founding Pastor, Life.Church · Bestselling Author',
      bio: 'Globally recognized as a leader of leaders, Craig Groeschel is the founding and senior pastor of Life.Church, a three-time Gallup Exceptional Workplace Award recipient. Known for its missional approach to leveraging the latest technology, Life.Church is the creator of the YouVersion Family of Bible Apps — installed on over a billion unique devices worldwide. He hosts the top-ranked "Craig Groeschel Leadership Podcast" and is a New York Times bestselling author. His latest book is "Heal Your Hurting Mind: Biblical Hope for Anxiety, Depression, Burnout, and the Emotions No One Talks About."',
      talkTitle: 'The Invisible Language of Leadership',
      talkDesc: 'Some of the most frustrating moments aren\'t new problems — they\'re familiar ones that refuse to go away. The same tension resurfaces. The same conversations repeat. The same outcomes show up again and again. What feels like a people problem is often something deeper. Join Craig Groeschel as he unpacks how the repeated behaviors leaders tolerate, reward and reinforce quietly shape culture and determine results. You\'ll learn how most leaders spend their energy solving issues without realizing they may be reinforcing the very dynamics that created them. Discover why what you repeat becomes what you produce and how to recognize what\'s really driving your outcomes, disrupt what isn\'t working, intentionally design better rhythms and reinforce what moves your mission forward.'
    },
    {
      id: 'jim',
      img: 'Jim_Collins.jpg',
      name: 'Jim Collins',
      role: 'Management Educator · Socratic Advisor · Bestselling Author',
      bio: 'Jim Collins is a student and teacher of exceptional human endeavor and a Socratic advisor to leaders across all sectors. Having invested more than three decades in rigorous research, he has authored or coauthored books that have sold more than 11 million copies, including the #1 bestseller "Good to Great" and the enduring classic "Built to Last." His newest book, available April 2026, tackles the big question implicit in its title: "What to Make of a Life." In 1995, Collins founded a management laboratory where he conducts research and engages with CEOs and senior leadership teams. Concepts that emerged from his earlier work have become part of the leadership lexicon, such as Level 5 Leadership, the right people on the bus, the Hedgehog Concept, the Flywheel and Big Hairy Audacious Goals. In 2017, Forbes selected him as one of the 100 Greatest Living Business Minds.',
      talkTitle: 'What to Make of a Life',
      talkDesc: 'At some point every life encounters cliffs — moments when something ends, unravels or changes course without warning. Success can give way to scandal. Purpose can stall. Clarity can dissolve into fog. And in those disorienting seasons, the question becomes deeply personal: What do you make of this life now? Join Jim Collins as he shares what he uncovered through a decade-long research project exploring how lives are built, fractured and rebuilt through inevitable turning points. Studying remarkable lives at critical inflection points, he reveals how you can reignite the inner fire and build renewed momentum across a lifetime. You\'ll gain a framework for navigating fog, overcoming fracture points and discovering a role you are uniquely encoded to fulfill — even if you must find one more than once. Leave with practical wisdom to rebuild after setbacks, sustain purpose and shape a life that remains creative, resilient and deeply fulfilling over its long arc.'
    },
    {
      id: 'vanessa',
      img: 'Vanessa_Van_Edwards.jpg',
      name: 'Vanessa Van Edwards',
      role: 'Behavioral Researcher · Harvard Instructor · International Bestselling Author',
      bio: 'Vanessa Van Edwards is a renowned behavioral researcher, international bestselling author and instructor at Harvard University. Her books "Captivate: The Science of Succeeding with People" and "Cues: Master the Secret Language of Charismatic Communication" have been translated into over 18 languages. More than 100 million people watch her YouTube tutorials and TEDx Talk.\n\nVan Edwards shares tangible skills to improve interpersonal communication and leadership. She has taught science-backed people skills to audiences worldwide, including Harvard, SXSW, MIT and Stanford. Hundreds of thousands of students have taken her popular communication courses on LinkedIn Learning, and thousands have taken her advanced social skills course, "People School." Van Edwards has also been featured on CNN, BBC, CBS Mornings, Fast Company, Inc. Magazine, Entrepreneur Magazine, USA Today, The Today Show and many more. She regularly speaks to innovative companies, including Google, Facebook, Comcast, Frito Lay, Microsoft, Amazon and Univision.',
      talkTitle: 'The Surprising Micro-Habits of High-Impact Leaders',
      talkDesc: 'Many leaders work hard to increase their impact — yet the most effective leaders know how to focus their energy on what matters most. The difference isn\'t more effort, but applying effort in the right ways with the right tools. Join Vanessa Van Edwards as she shares insights from two decades of research on high-performing leaders, revealing the patterns that consistently drive results. Unpack the micro-habits, rituals and mindsets that set standout leaders apart — and learn how small shifts in behavior can create outsized results. Walk away with five practical ways to strengthen your influence and leverage your expert power.'
    },
    {
      id: 'kwame',
      img: 'Kwame_Christian.jpg',
      name: 'Kwame Christian',
      role: 'CEO, American Negotiation Institute · Bestselling Author',
      bio: 'Kwame Christian is a bestselling author and CEO of the American Negotiation Institute. His top negotiation podcast, "Negotiate Anything," has over 15 million downloads in 180+ countries. His books, "Finding Confidence in Conflict" and "How to Have Difficult Conversations About Race," are both bestsellers that have made significant impacts.\n\nAs a LinkedIn Top Voice, Christian partnered with LinkedIn to create the platform\'s only negotiation certificate program — a certificate that has been earned by more than 30,000 negotiators worldwide to date. Featured in Forbes, NPR, USA Today and CNBC, Christian has collaborated with Fortune 500 companies like Google, Apple and NASA. Using his personal journey from a people pleaser to a master negotiator, Christian will empower you to handle challenging conversations confidently.',
      talkTitle: 'Why Being Right Isn\'t Enough',
      talkDesc: 'Every leader has moments when the conversation doesn\'t go how they hoped. The facts are solid. The logic is sound. And still — the room goes quiet, resistance rises or alignment slips away. When influence breaks down, the issue is often relational, not informational. Drawing from his experience as a lawyer, mediator and negotiation expert, Kwame Christian helps leaders rethink how they approach high-stake conversations. You\'ll discover how everyday interactions become micro-negotiations that either build trust or create distance. Using Christian\'s Compassionate Curiosity™ framework, you\'ll walk away with practical tools to overcome emotional and psychological barriers, reduce defensiveness and move people toward agreement.'
    },
    {
      id: 'andy',
      img: 'Andy_Stanley.jpg',
      name: 'Andy Stanley',
      role: 'Founder, North Point Ministries · Pastor · Bestselling Author',
      bio: 'Andy Stanley is a globally renowned pastor, communicator, bestselling author and leadership expert. He founded North Point Ministries in Atlanta in 1995, which has grown to eight church campuses in metro Atlanta and a network of more than 210 churches globally, serving hundreds of thousands of people weekly. As the host of "Your Move," Stanley brings clarity to complex cultural issues using a practical communication style built on helping people make better decisions and live a better life. And as the voice behind "The Andy Stanley Leadership Podcast," he provides pragmatic wisdom and advice to help leaders go further faster.\n\nMillions of his messages are consumed monthly through television, YouTube, podcasts and more. Stanley is a bestselling author of over 20 books, including "Not In It to Win It," "Better Decisions, Fewer Regrets" and "Visioneering." His latest book, "What Great Leaders Do," releases August 2026.',
      talkTitle: 'What Great Leaders Do',
      talkDesc: 'While it\'s true that leaders are learners, they are doers as well. That\'s what distinguishes a leader from the rest of the pack. Doing is what makes the difference. Which leads us to ask, what do great leaders do? Join Andy Stanley as he explores the answer to this important question. As both an accomplished leader and student of leadership you\'ll find his answer to be insightful, helpful and ultimately practical. Because after all, leaders are doers!'
    },
    {
      id: 'ryan',
      img: 'Ryan_Leak.jpg',
      name: 'Ryan Leak',
      role: 'CEO, Ryan Leak Group · New York Times Bestselling Author',
      bio: 'Ryan Leak is a New York Times bestselling author, podcast host and strategic advisor renowned for his ability to inspire and transform individuals and organizations. As the CEO of a leadership development firm based in Dallas, Leak and his team train over 30,000 leaders annually — from Fortune 500 companies to professional sports teams — using research-backed strategies that drive real results. He is the author of "Chasing Failure," "Leveling Up" and his latest release, "How to Work With Complicated People."\n\nLeak also hosts "The Ryan Leak Podcast," where he shares short, impactful leadership insights drawn from his coaching, research and real-world experience. His work has been featured in numerous media outlets, including "Good Morning America" and "The Today Show." Whether through his powerful keynote speeches, bestselling books, podcast or social media presence, Ryan Leak inspires over 50,000 individuals every month to reach their God-given potential.',
      talkTitle: 'Building a Culture They Never Want to Leave',
      talkDesc: 'People rarely leave a job suddenly — they leave mentally and emotionally long before they turn in their resignation. Disengagement shows up quietly and is often missed by leaders who focus on retention instead of culture. In this talk, Ryan Leak unpacks research and real-world insights on what the best leaders do to build environments where people don\'t just stay, but grow and flourish. Learn how to engage your team early, recognize warning signs before turnover happens and create conditions where staying feels like progress — not compromise. Walk away with practical language, simple frameworks and actionable steps to build a culture where people do their best work and reach their full potential.'
    },
    {
      id: 'arthur',
      img: 'Arthur_C__Brooks.jpg',
      name: 'Arthur C. Brooks',
      role: 'Harvard Professor · CBS News Contributor · Bestselling Author',
      bio: 'Arthur C. Brooks is a professor at the Harvard Kennedy School and the Harvard Business School, where he teaches courses on leadership and happiness. He is also a CBS News Contributor, columnist with The Free Press and host of the weekly podcast "Office Hours With Arthur Brooks."\n\nBrooks is the author of 16 books, including the #1 New York Times bestsellers "Build the Life You Want," coauthored with Oprah Winfrey, and "From Strength to Strength: Finding Success, Happiness, and Deep Purpose in the Second Half of Life." His most recent book, "The Meaning of Your Life: Finding Purpose in an Age of Emptiness," released March 31.\n\nBrooks is one of the world\'s leading experts on the science of human happiness, teaching people in private companies, universities, public agencies and faith communities how they can live happier lives and bring greater well-being to others.',
      talkTitle: 'The Science of Happiness & Leadership',
      talkDesc: 'Arthur C. Brooks brings the rigorous science of human happiness into the leadership conversation. Drawing on decades of academic research and real-world application, he reveals how leaders can cultivate deeper purpose, greater well-being and more meaningful impact — both for themselves and for the people they lead. Learn how to move from success-driven striving to a life of genuine significance, and discover what the science tells us about sustaining joy and purpose across the full arc of a leadership career.'
    },
    {
      id: 'alison',
      img: 'Alison_Levine.jpg',
      name: 'Alison Levine',
      role: 'Polar Explorer · Mountaineer · West Point Faculty · Bestselling Author',
      bio: 'Alison Levine has climbed the highest peak on every continent, served as the team captain of the first American Women\'s Everest Expedition and skied across the Arctic Circle to the geographic North Pole. In January 2008, she made history as the first American to follow a remote route across west Antarctica for 600 miles to the South Pole. Her success in extreme environments is noteworthy given she had three heart surgeries and suffers from a neurological disease that causes the arteries that feed her fingers and toes to collapse in cold weather, leaving her at extreme risk for frostbite.\n\nLevine spent four years as an adjunct professor at the United States Military Academy at West Point, where she focused on leading teams in extreme environments. She is the author of the New York Times bestseller "On the Edge."',
      talkTitle: 'Leadership at the Edge of Human Endurance',
      talkDesc: 'What does it take to lead when the environment is unforgiving, the stakes are life and death, and every decision matters? Alison Levine draws on her extraordinary experiences in the most extreme environments on earth to reveal leadership lessons that apply powerfully to everyday organizational challenges. From the summit of Everest to the South Pole, she demonstrates how courage, adaptability and team trust determine success — and how the principles that work at altitude translate directly to the boardroom, the field and every team facing high-pressure circumstances.'
    }
  ];

  const speakerOrder = ['craig','jim','vanessa','kwame','andy','ryan','arthur','alison'];
  let currentSpeakerIdx = 0;

  function openSpeaker(id) {
    currentSpeakerIdx = speakerOrder.indexOf(id);
    renderSpeaker(currentSpeakerIdx);
    document.getElementById('spOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeSpeaker() {
    document.getElementById('spOverlay').classList.remove('open');
    document.body.style.overflow = '';
  }

  function handleSpOverlay(e) {
    if (e.target === document.getElementById('spOverlay')) closeSpeaker();
  }

  function navSpeaker(dir) {
    currentSpeakerIdx = (currentSpeakerIdx + dir + speakers.length) % speakers.length;
    renderSpeaker(currentSpeakerIdx);
    // scroll content back to top
    document.querySelector('.sp-content').scrollTop = 0;
  }

  function renderSpeaker(idx) {
    const s = speakers[idx];
    const img = document.getElementById('sp-img');
    img.style.opacity = '0';
    setTimeout(() => { img.src = 'Speakers/' + s.img; img.alt = s.name; img.style.opacity = '1'; }, 120);
    img.style.transition = 'opacity 0.3s ease';

    document.getElementById('sp-name').textContent = s.name;
    document.getElementById('sp-role').textContent = s.role;

    // Render bio with paragraph breaks
    const bioEl = document.getElementById('sp-bio');
    bioEl.innerHTML = s.bio.split('\n\n').map(p => `<p>${p}</p>`).join('');

    document.getElementById('sp-talk-title').textContent = s.talkTitle;
    document.getElementById('sp-talk-desc').textContent  = s.talkDesc;
    document.getElementById('sp-cur').textContent   = idx + 1;
    document.getElementById('sp-total').textContent = speakers.length;

    document.getElementById('sp-talk-wrap').style.display = s.talkTitle ? 'block' : 'none';
  }

  // keyboard nav
  document.addEventListener('keydown', e => {
    const overlay = document.getElementById('spOverlay');
    if (!overlay.classList.contains('open')) return;
    if (e.key === 'Escape')     closeSpeaker();
    if (e.key === 'ArrowRight') navSpeaker(1);
    if (e.key === 'ArrowLeft')  navSpeaker(-1);
  });