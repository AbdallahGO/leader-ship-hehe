-- Seed plans
INSERT INTO plans (key, name, price, description, is_active)
VALUES (
        'local',
        'Local Admission',
        199.00,
        'In-person ticket for local attendees.',
        TRUE
    ),
    (
        'studio',
        'Studio Experience',
        259.00,
        'Premium in-person ticket with studio seating.',
        TRUE
    ),
    (
        'online',
        'Online Access',
        199.00,
        'Virtual event access and recordings.',
        TRUE
    ),
    (
        'team',
        'Team Pass',
        179.00,
        'Group ticket for teams of four or more.',
        TRUE
    ) ON CONFLICT (key) DO NOTHING;
-- Seed promo codes
INSERT INTO promo_codes (
        code,
        discount_type,
        discount_value,
        max_uses,
        expires_at,
        is_active
    )
VALUES (
        'APEX20',
        'percentage',
        20.00,
        500,
        NOW() + INTERVAL '90 days',
        TRUE
    ),
    (
        'SUMMIT10',
        'percentage',
        10.00,
        500,
        NOW() + INTERVAL '90 days',
        TRUE
    ) ON CONFLICT (code) DO NOTHING;
-- Seed speakers
INSERT INTO speakers (
        name,
        role,
        bio,
        talk_title,
        talk_desc,
        image_url,
        order_index,
        is_active
    )
VALUES (
        'Craig Groeschel',
        'Founder & Lead Pastor',
        'Craig is a TED speaker and founder of Life.Church.',
        'Leading in Uncertainty',
        'How leaders can navigate change with confidence.',
        'craig-groeschel.jpg',
        1,
        TRUE
    ),
    (
        'Jim Collins',
        'Author & Researcher',
        'Jim writes about great companies and enduring leadership.',
        'Built to Last',
        'The discipline required to build lasting organizations.',
        'jim-collins.jpg',
        2,
        TRUE
    ),
    (
        'Vanessa Van Edwards',
        'Behavioral Investigator',
        'Vanessa studies human behavior and communication.',
        'Power of Human Connection',
        'Practical techniques for building trust and influence.',
        'vanessa-van-edwards.jpg',
        3,
        TRUE
    ),
    (
        'Kwame Christian',
        'Negotiation Strategist',
        'Kwame teaches leaders how to solve conflict and negotiate.',
        'Negotiation as Leadership',
        'Winning the room by putting people first.',
        'kwame-christian.jpg',
        4,
        TRUE
    ),
    (
        'Andy Stanley',
        'Leadership Speaker',
        'Andy helps teams lead with clarity and purpose.',
        'Courageous Decisions',
        'How leaders make hard choices without losing the team.',
        'andy-stanley.jpg',
        5,
        TRUE
    ),
    (
        'Ryan Leak',
        'Leadership Coach',
        'Ryan focuses on building resilient leaders and healthy culture.',
        'Leading with Empathy',
        'Developing a culture where people can thrive.',
        'ryan-leak.jpg',
        6,
        TRUE
    ),
    (
        'Arthur C. Brooks',
        'Author & Professor',
        'Arthur explores happiness, purpose, and leadership.',
        'The Joy of Leadership',
        'What thriving leaders do to stay inspired and effective.',
        'arthur-c-brooks.jpg',
        7,
        TRUE
    ),
    (
        'Alison Levine',
        'Explorer & Speaker',
        'Alison is an adventurer and leadership expert.',
        'Leading with Resilience',
        'Lessons from extreme expeditions applied to leadership.',
        'alison-levine.jpg',
        8,
        TRUE
    ) ON CONFLICT (name) DO NOTHING;
-- Seed sponsors
INSERT INTO sponsors (name, logo_url, website, tier, is_active)
VALUES (
        'World Vision',
        '/images/sponsors/world-vision.png',
        'https://www.worldvision.org',
        'Platinum',
        TRUE
    ),
    (
        'Thrivent',
        '/images/sponsors/thrivent.png',
        'https://www.thrivent.com',
        'Gold',
        TRUE
    ),
    (
        'GiveSendGo',
        '/images/sponsors/givesendgo.png',
        'https://www.givesendgo.com',
        'Gold',
        TRUE
    ),
    (
        'Pull Spark',
        '/images/sponsors/pull-spark.png',
        'https://www.pull-spark.com',
        'Silver',
        TRUE
    ),
    (
        'RightNow Media',
        '/images/sponsors/rightnow-media.png',
        'https://www.rightnowmedia.org',
        'Bronze',
        TRUE
    ),
    (
        'C12 Group',
        '/images/sponsors/c12-group.png',
        'https://www.c12group.com',
        'Bronze',
        TRUE
    ),
    (
        'Convene',
        '/images/sponsors/convene.png',
        'https://www.convene.com',
        'Silver',
        TRUE
    ),
    (
        'ECFA',
        '/images/sponsors/ecfa.png',
        'https://www.ecfa.org',
        'Silver',
        TRUE
    ) ON CONFLICT (name) DO NOTHING;