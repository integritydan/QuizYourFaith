-- Comprehensive Bible Books Schema for QuizYourFaith
-- All 66 books of the Bible with detailed categorization

-- Bible Books Master Table
CREATE TABLE bible_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    testament ENUM('old', 'new') NOT NULL,
    book_order INT NOT NULL,
    chapters INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    subcategory VARCHAR(50),
    description TEXT,
    key_verses TEXT,
    theme TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    icon VARCHAR(50) DEFAULT 'book',
    is_active BOOLEAN DEFAULT TRUE,
    quiz_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_book (name),
    INDEX idx_testament (testament),
    INDEX idx_category (category),
    INDEX idx_order (book_order),
    INDEX idx_active (is_active)
);

-- Bible Book Categories
CREATE TABLE bible_book_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    testament ENUM('old', 'new', 'both') NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6c757d',
    icon VARCHAR(50) DEFAULT 'book',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert Bible Book Categories
INSERT INTO bible_book_categories (name, display_name, testament, description, color, icon, sort_order) VALUES
('pentateuch', 'Pentateuch (Torah)', 'old', 'The first five books of the Bible, also known as the Torah', '#8B4513', 'scroll', 1),
('historical', 'Historical Books', 'old', 'Books recording the history of Israel and God''s people', '#CD853F', 'landmark', 2),
('wisdom', 'Wisdom Literature', 'old', 'Poetic and wisdom writings including Psalms and Proverbs', '#4682B4', 'feather', 3),
('major_prophets', 'Major Prophets', 'old', 'Longer prophetic books including Isaiah, Jeremiah, Ezekiel', '#DC143C', 'megaphone', 4),
('minor_prophets', 'Minor Prophets', 'old', 'Shorter prophetic books from Hosea to Malachi', '#FF6347', 'bell', 5),
('gospels', 'Gospels', 'new', 'The four accounts of Jesus'' life and ministry', '#FFD700', 'cross', 6),
('acts', 'Acts & History', 'new', 'The early church history and Acts of the Apostles', '#32CD32', 'church', 7),
('pauline_epistles', 'Pauline Epistles', 'new', 'Letters written by the Apostle Paul', '#4169E1', 'envelope', 8),
('general_epistles', 'General Epistles', 'new', 'Letters written by other apostles', '#9370DB', 'envelope-open', 9),
('apocalyptic', 'Apocalyptic', 'new', 'Prophetic and apocalyptic literature', '#FF1493', 'eye', 10);

-- Insert All 66 Books of the Bible
INSERT INTO bible_books (name, short_name, testament, book_order, chapters, category, subcategory, description, key_verses, theme, color, icon) VALUES

-- OLD TESTAMENT (39 Books)
-- Pentateuch (5 books)
('Genesis', 'Gen', 'old', 1, 50, 'pentateuch', 'creation', 'The book of beginnings, covering creation, the patriarchs, and the foundation of Israel', 'Genesis 1:1 - "In the beginning God created the heavens and the earth."', 'God''s creative power and covenant promises', '#8B4513', 'seedling'),
('Exodus', 'Exo', 'old', 2, 40, 'pentateuch', 'deliverance', 'The story of Israel''s deliverance from Egypt and the giving of the Law', 'Exodus 3:14 - "I AM WHO I AM"', 'God''s deliverance and presence with His people', '#A0522D', 'hands-helping'),
('Leviticus', 'Lev', 'old', 3, 27, 'pentateuch', 'holiness', 'Instructions for holy living and worship for the Israelites', 'Leviticus 19:2 - "You shall be holy, for I the Lord your God am holy."', 'Holiness and proper worship', '#CD853F', 'fire'),
('Numbers', 'Num', 'old', 4, 36, 'pentateuch', 'wandering', 'The census and wilderness wanderings of Israel', 'Numbers 6:24-26 - "The Lord bless you and keep you..."', 'God''s faithfulness during wilderness times', '#D2B48C', 'compass'),
('Deuteronomy', 'Deut', 'old', 5, 34, 'pentateuch', 'covenant', 'Moses'' farewell speech and renewal of the covenant', 'Deuteronomy 6:4-5 - "Hear, O Israel: The Lord our God, the Lord is one!"', 'Obedience to God''s covenant', '#DEB887', 'mountain'),

-- Historical Books (12 books)
('Joshua', 'Josh', 'old', 6, 24, 'historical', 'conquest', 'The conquest and division of the Promised Land', 'Joshua 1:9 - "Be strong and courageous. Do not be afraid..."', 'God''s faithfulness in fulfilling promises', '#BC8F8F', 'flag'),
('Judges', 'Judg', 'old', 7, 21, 'historical', 'cycles', 'The cycle of Israel''s apostasy and deliverance', 'Judges 21:25 - "In those days there was no king in Israel"', 'The consequences of disobedience', '#F4A460', 'balance-scale'),
('Ruth', 'Ruth', 'old', 8, 4, 'historical', 'redemption', 'The story of Ruth''s loyalty and God''s providence', 'Ruth 1:16 - "Where you go I will go, and where you stay I will stay."', 'God''s redemption and faithful love', '#D2691E', 'heart'),
('1 Samuel', '1 Sam', 'old', 9, 31, 'historical', 'transition', 'The transition from judges to kings, focusing on Samuel and Saul', '1 Samuel 16:7 - "The Lord does not look at the things people look at."', 'God looks at the heart, not appearance', '#B8860B', 'crown'),
('2 Samuel', '2 Sam', 'old', 10, 24, 'historical', 'david', 'The reign of King David', '2 Samuel 7:22 - "How great you are, Sovereign Lord!"', 'God''s covenant with David', '#DAA520', 'star'),
('1 Kings', '1 Kings', 'old', 11, 22, 'historical', 'united_kingdom', 'The reigns of Solomon and the divided kingdom', '1 Kings 8:27 - "But will God really dwell on earth?"', 'God''s presence among His people', '#FFD700', 'temple'),
('2 Kings', '2 Kings', 'old', 12, 25, 'historical', 'divided_kingdom', 'The history of Israel and Judah until the exile', '2 Kings 17:13 - "Turn from your evil ways."', 'The consequences of persistent sin', '#F0E68C', 'broken-heart'),
('1 Chronicles', '1 Chron', 'old', 13, 29, 'historical', 'genealogy', 'Genealogies and David''s reign from a priestly perspective', '1 Chronicles 29:11 - "Yours, Lord, is the greatness and the power..."', 'God''s sovereignty over history', '#EEE8AA', 'family'),
('2 Chronicles', '2 Chron', 'old', 14, 36, 'historical', 'temple_focus', 'History of Judah focusing on the temple and religious reforms', '2 Chronicles 7:14 - "If my people, who are called by my name..."', 'Repentance and restoration', '#F5DEB3', 'church'),
('Ezra', 'Ezra', 'old', 15, 10, 'historical', 'restoration', 'The return from exile and rebuilding of the temple', 'Ezra 7:10 - "Ezra had devoted himself to the study and observance of the Law."', 'The importance of God''s Word', '#FFE4B5', 'hammer'),
('Nehemiah', 'Neh', 'old', 16, 13, 'historical', 'rebuilding', 'The rebuilding of Jerusalem''s walls', 'Nehemiah 8:10 - "The joy of the Lord is your strength."', 'God''s strength in difficult tasks', '#FFDEAD', 'wall'),
('Esther', 'Esth', 'old', 17, 10, 'historical', 'providence', 'God''s providence in saving the Jews from destruction', 'Esther 4:14 - "Who knows but that you have come to your royal position for such a time as this?"', 'God''s providence in difficult circumstances', '#FFE4E1', 'crown'),

-- Wisdom Literature (5 books)
('Job', 'Job', 'old', 18, 42, 'wisdom', 'suffering', 'The problem of suffering and God''s sovereignty', 'Job 19:25 - "I know that my redeemer lives!"', 'Faith in God despite suffering', '#4682B4', 'question'),
('Psalms', 'Ps', 'old', 19, 150, 'wisdom', 'praise', 'Songs and prayers for worship and life', 'Psalm 23:1 - "The Lord is my shepherd, I lack nothing."', 'Praise, prayer, and worship', '#5F9EA0', 'music'),
('Proverbs', 'Prov', 'old', 20, 31, 'wisdom', 'wisdom', 'Practical wisdom for daily living', 'Proverbs 3:5-6 - "Trust in the Lord with all your heart..."', 'Godly wisdom for life decisions', '#6495ED', 'lightbulb'),
('Ecclesiastes', 'Eccles', 'old', 21, 12, 'wisdom', 'meaning', 'The search for meaning in life', 'Ecclesiastes 12:13 - "Fear God and keep his commandments."', 'True meaning found in God', '#00CED1', 'hourglass'),
('Song of Solomon', 'Song', 'old', 22, 8, 'wisdom', 'love', 'A celebration of romantic love and marriage', 'Song of Solomon 2:16 - "My beloved is mine and I am his."', 'God''s gift of romantic love', '#48D1CC', 'heart'),

-- Major Prophets (5 books)
('Isaiah', 'Isa', 'old', 23, 66, 'major_prophets', 'salvation', 'Prophecies of judgment and salvation', 'Isaiah 53:5 - "But he was pierced for our transgressions."', 'God''s plan of salvation', '#DC143C', 'scroll'),
('Jeremiah', 'Jer', 'old', 24, 52, 'major_prophets', 'judgment', 'Warnings of judgment and calls to repentance', 'Jeremiah 29:11 - "For I know the plans I have for you..."', 'God''s plans for His people', '#B22222', 'warning'),
('Lamentations', 'Lam', 'old', 25, 5, 'major_prophets', 'lament', 'Laments over the destruction of Jerusalem', 'Lamentations 3:22-23 - "His compassions never fail."', 'God''s faithfulness in judgment', '#CD5C5C', 'tear'),
('Ezekiel', 'Ezek', 'old', 26, 48, 'major_prophets', 'glory', 'Visions of God''s glory and future restoration', 'Ezekiel 36:26 - "I will give you a new heart and put a new spirit in you."', 'God''s power to transform lives', '#F08080', 'eye'),
('Daniel', 'Dan', 'old', 27, 12, 'major_prophets', 'sovereignty', 'God''s sovereignty in human history and prophecy', 'Daniel 2:20 - "Praise be to the name of God for ever and ever."', 'God''s sovereignty over nations', '#FA8072', 'crown'),

-- Minor Prophets (12 books)
('Hosea', 'Hos', 'old', 28, 14, 'minor_prophets', 'faithful_love', 'God''s faithful love for unfaithful Israel', 'Hosea 6:6 - "I desire mercy, not sacrifice."', 'God''s faithful love', '#FF6347', 'heart-broken'),
('Joel', 'Joel', 'old', 29, 3, 'minor_prophets', 'day_of_lord', 'The Day of the Lord and the outpouring of the Spirit', 'Joel 2:28 - "I will pour out my Spirit on all people."', 'The promise of the Holy Spirit', '#FF7F50', 'fire'),
('Amos', 'Amos', 'old', 30, 9, 'minor_prophets', 'social_justice', 'Social justice and judgment on Israel''s neighbors', 'Amos 5:24 - "Let justice roll on like a river."', 'God''s concern for social justice', '#FF8C00', 'balance-scale'),
('Obadiah', 'Obad', 'old', 31, 1, 'minor_prophets', 'edom', 'Judgment on Edom for attacking Israel', 'Obadiah 1:15 - "The day of the Lord is near for all nations."', 'God''s judgment on pride', '#FFA500', 'mountain'),
('Jonah', 'Jonah', 'old', 32, 4, 'minor_prophets', 'mercy', 'God''s mercy to Nineveh through reluctant Jonah', 'Jonah 2:9 - "Salvation comes from the Lord."', 'God''s mercy to all nations', '#FFB347', 'fish'),
('Micah', 'Mic', 'old', 33, 7, 'minor_prophets', 'true_religion', 'True religion versus empty ritual', 'Micah 6:8 - "Act justly, love mercy, walk humbly with your God."', 'The essence of true religion', '#FFC0CB', 'walking'),
('Nahum', 'Nah', 'old', 34, 3, 'minor_prophets', 'nineveh', 'The fall of Nineveh and God''s justice', 'Nahum 1:7 - "The Lord is good, a refuge in times of trouble."', 'God''s justice and protection', '#FFDAB9', 'city'),
('Habakkuk', 'Hab', 'old', 35, 3, 'minor_prophets', 'faith', 'Faith in God despite unanswered questions', 'Habakkuk 2:4 - "The righteous will live by faith."', 'Living by faith', '#FFE4B5', 'question'),
('Zephaniah', 'Zeph', 'old', 36, 3, 'minor_prophets', 'remnant', 'The Day of the Lord and the faithful remnant', 'Zephaniah 3:17 - "The Lord your God is with you, the Mighty Warrior who saves."', 'God''s presence with His remnant', '#FFF8DC', 'shield'),
('Haggai', 'Hag', 'old', 37, 2, 'minor_prophets', 'priorities', 'Encouragement to rebuild the temple', 'Haggai 1:8 - "Go up into the mountains and bring down timber."', 'Prioritizing God''s work', '#FFFACD', 'hammer'),
('Zechariah', 'Zech', 'old', 38, 14, 'minor_prophets', 'restoration', 'Visions of Jerusalem''s restoration and Messiah', 'Zechariah 9:9 - "See, your king comes to you, righteous and victorious."', 'The coming of the Messiah', '#FFFAF0', 'crown'),
('Malachi', 'Mal', 'old', 39, 4, 'minor_prophets', 'covenant', 'God''s covenant faithfulness and coming judgment', 'Malachi 3:6 - "I the Lord do not change."', 'God''s unchanging nature', '#FFFFFF', 'fire'),

-- NEW TESTAMENT (27 Books)
-- Gospels (4 books)
('Matthew', 'Matt', 'new', 40, 28, 'gospels', 'king', 'Jesus as the promised King and fulfillment of prophecy', 'Matthew 28:18-20 - "All authority in heaven and on earth has been given to me."', 'Jesus as King and fulfillment of prophecy', '#FFD700', 'crown'),
('Mark', 'Mark', 'new', 41, 16, 'gospels', 'servant', 'Jesus as the suffering Servant and Son of God', 'Mark 10:45 - "For even the Son of Man did not come to be served, but to serve."', 'Jesus as the suffering Servant', '#FFA500', 'hand-holding-heart'),
('Luke', 'Luke', 'new', 42, 24, 'gospels', 'son_of_man', 'Jesus as the perfect Son of Man who seeks the lost', 'Luke 19:10 - "For the Son of Man came to seek and to save the lost."', 'Jesus'' compassion for the lost', '#FF8C00', 'search'),
('John', 'John', 'new', 43, 21, 'gospels', 'son_of_god', 'Jesus as the divine Son of God and source of eternal life', 'John 3:16 - "For God so loved the world that he gave his one and only Son."', 'Jesus as the divine Son of God', '#FF6347', 'cross'),

-- Acts (1 book)
('Acts', 'Acts', 'new', 44, 28, 'acts', 'holy_spirit', 'The birth and growth of the church through the Holy Spirit', 'Acts 1:8 - "You will receive power when the Holy Spirit comes on you."', 'The power of the Holy Spirit in the church', '#32CD32', 'dove'),

-- Pauline Epistles (13 books)
('Romans', 'Rom', 'new', 45, 16, 'pauline_epistles', 'righteousness', 'The gospel of righteousness by faith', 'Romans 1:16-17 - "I am not ashamed of the gospel."', 'Righteousness by faith', '#4169E1', 'balance-scale'),
('1 Corinthians', '1 Cor', 'new', 46, 16, 'pauline_epistles', 'church_problems', 'Solutions to problems in the Corinthian church', '1 Corinthians 13:13 - "And now these three remain: faith, hope and love."', 'Love as the greatest virtue', '#4682B4', 'heart'),
('2 Corinthians', '2 Cor', 'new', 47, 13, 'pauline_epistles', 'ministry', 'Paul''s defense of his apostolic ministry', '2 Corinthians 12:9 - "My grace is sufficient for you."', 'God''s power in human weakness', '#5F9EA0', 'hands-praying'),
('Galatians', 'Gal', 'new', 48, 6, 'pauline_epistles', 'freedom', 'Freedom from the law through faith in Christ', 'Galatians 5:1 - "It is for freedom that Christ has set us free."', 'Freedom in Christ', '#6495ED', 'broken-chain'),
('Ephesians', 'Eph', 'new', 49, 6, 'pauline_epistles', 'church', 'The mystery of the church as Christ''s body', 'Ephesians 2:8-9 - "For it is by grace you have been saved."', 'Salvation by grace through faith', '#00CED1', 'church'),
('Philippians', 'Phil', 'new', 50, 4, 'pauline_epistles', 'joy', 'Joy in Christ despite difficult circumstances', 'Philippians 4:13 - "I can do all this through him who gives me strength."', 'Joy and strength in Christ', '#48D1CC', 'smile'),
('Colossians', 'Col', 'new', 51, 4, 'pauline_epistles', 'supremacy', 'The supremacy of Christ over all things', 'Colossians 1:15-16 - "The Son is the image of the invisible God."', 'Christ''s supremacy over all', '#40E0D0', 'crown'),
('1 Thessalonians', '1 Thess', 'new', 52, 5, 'pauline_epistles', 'hope', 'Encouragement and instruction for new believers', '1 Thessalonians 4:16-17 - "The Lord himself will come down from heaven."', 'Hope in Christ''s return', '#00FFFF', 'cloud'),
('2 Thessalonians', '2 Thess', 'new', 53, 3, 'pauline_epistles', 'perseverance', 'Correction and encouragement regarding Christ''s return', '2 Thessalonians 3:3 - "The Lord is faithful."', 'God''s faithfulness', '#E0FFFF', 'shield'),
('1 Timothy', '1 Tim', 'new', 54, 6, 'pauline_epistles', 'leadership', 'Instructions for church leadership and organization', '1 Timothy 3:15 - "The church of the living God, the pillar and foundation of the truth."', 'Church leadership and godliness', '#AFEEEE', 'users'),
('2 Timothy', '2 Tim', 'new', 55, 4, 'pauline_epistles', 'legacy', 'Paul''s final charge to Timothy to continue the ministry', '2 Timothy 3:16-17 - "All Scripture is God-breathed."', 'The authority and power of Scripture', '#B0E0E6', 'book'),
('Titus', 'Titus', 'new', 56, 3, 'pauline_epistles', 'good_works', 'Instructions for church order and good works', 'Titus 3:5 - "He saved us through the washing of rebirth and renewal by the Holy Spirit."', 'Salvation by grace, not works', '#ADD8E6', 'hands-wash'),
('Philemon', 'Philem', 'new', 57, 1, 'pauline_epistles', 'forgiveness', 'A personal appeal for forgiveness and reconciliation', 'Philemon 1:6 - "I pray that you may be active in sharing your faith."', 'Forgiveness and reconciliation', '#87CEEB', 'handshake'),

-- General Epistles (8 books)
('Hebrews', 'Heb', 'new', 58, 13, 'general_epistles', 'better_covenant', 'The superiority of Christ and the new covenant', 'Hebrews 11:1 - "Faith is confidence in what we hope for."', 'The superiority of Christ''s covenant', '#9370DB', 'trophy'),
('James', 'James', 'new', 59, 5, 'general_epistles', 'practical_faith', 'Practical faith that works through love', 'James 2:17 - "Faith by itself, if it is not accompanied by action, is dead."', 'Faith demonstrated through works', '#8A2BE2', 'tools'),
('1 Peter', '1 Pet', 'new', 60, 5, 'general_epistles', 'suffering', 'Encouragement for suffering Christians', '1 Peter 2:9 - "You are a chosen people, a royal priesthood."', 'Identity and hope in suffering', '#9400D3', 'shield'),
('2 Peter', '2 Pet', 'new', 61, 3, 'general_epistles', 'false_teachers', 'Warnings against false teachers and encouragement to grow', '2 Peter 3:18 - "Grow in the grace and knowledge of our Lord."', 'Spiritual growth and discernment', '#9932CC', 'seedling'),
('1 John', '1 John', 'new', 62, 5, 'general_epistles', 'love', 'Tests of true Christianity and God''s love', '1 John 4:8 - "God is love."', 'God''s nature as love', '#BA55D3', 'heart'),
('2 John', '2 John', 'new', 63, 1, 'general_epistles', 'truth', 'Walking in truth and love', '2 John 1:6 - "And this is love: that we walk in obedience to his commands."', 'Truth and love in Christian living', '#DDA0DD', 'path'),
('3 John', '3 John', 'new', 64, 1, 'general_epistles', 'hospitality', 'The importance of hospitality and support for missionaries', '3 John 1:4 - "I have no greater joy than to hear that my children are walking in the truth."', 'Joy in spiritual children''s growth', '#E6E6FA', 'home'),
('Jude', 'Jude', 'new', 65, 1, 'general_epistles', 'contend', 'Contending for the faith against false teachers', 'Jude 1:24 - "To him who is able to keep you from stumbling."', 'God''s power to preserve believers', '#F8F8FF', 'shield'),

-- Apocalyptic (1 book)
('Revelation', 'Rev', 'new', 66, 22, 'apocalyptic', 'victory', 'The revelation of Jesus Christ and the end times', 'Revelation 21:4 - "He will wipe every tear from their eyes."', 'God''s ultimate victory and eternal kingdom', '#FF1493', 'crown');

-- Create quiz templates for each book category
CREATE TABLE bible_book_quiz_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    quiz_title VARCHAR(255) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    question_count INT DEFAULT 10,
    time_limit INT DEFAULT 600, -- 10 minutes default
    passing_score INT DEFAULT 70,
    badge_icon VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES bible_books(id) ON DELETE CASCADE,
    INDEX idx_book_quiz (book_id, difficulty),
    INDEX idx_active_templates (is_active)
);

-- Create quiz statistics table
CREATE TABLE bible_quiz_statistics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quiz_id INT,
    score INT NOT NULL,
    correct_answers INT NOT NULL,
    total_questions INT NOT NULL,
    completion_time INT, -- in seconds
    attempts INT DEFAULT 1,
    best_score INT,
    first_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    passed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES bible_books(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL,
    INDEX idx_user_book (user_id, book_id),
    INDEX idx_book_stats (book_id, score),
    INDEX idx_quiz_performance (quiz_id, score)
);

-- Create Bible mastery achievements
CREATE TABLE bible_mastery_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    badge_icon VARCHAR(255) NOT NULL,
    achievement_type ENUM('testament','category','book','total_books','perfect_score','streak') NOT NULL,
    requirement_value INT NOT NULL,
    reward_points INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert Bible mastery achievements
INSERT INTO bible_mastery_achievements (name, description, badge_icon, achievement_type, requirement_value, reward_points) VALUES
('Old Testament Scholar', 'Complete quizzes on all 39 Old Testament books', 'ot-scholar.png', 'testament', 39, 500),
('New Testament Expert', 'Complete quizzes on all 27 New Testament books', 'nt-expert.png', 'testament', 27, 500),
('Pentateuch Master', 'Complete all 5 books of Moses', 'pentateuch-master.png', 'category', 5, 200),
('Gospel Expert', 'Complete all 4 Gospels', 'gospel-expert.png', 'category', 4, 200),
('Major Prophet', 'Complete all Major Prophets', 'major-prophet.png', 'category', 5, 150),
('Minor Prophet', 'Complete all Minor Prophets', 'minor-prophet.png', 'category', 12, 300),
('Book Master', 'Complete 10 different Bible books', 'book-master.png', 'total_books', 10, 100),
('Book Champion', 'Complete 25 different Bible books', 'book-champion.png', 'total_books', 25, 250),
('Book Legend', 'Complete 50 different Bible books', 'book-legend.png', 'total_books', 50, 500),
('Perfect Score', 'Get 100% on any Bible book quiz', 'perfect-score.png', 'perfect_score', 1, 50),
('Quiz Streak', 'Get 90%+ on 5 consecutive Bible quizzes', 'quiz-streak.png', 'streak', 5, 100),
('Genesis Master', 'Complete Genesis with 80%+ score', 'genesis-master.png', 'book', 1, 75),
('Psalms Lover', 'Complete Psalms with 80%+ score', 'psalms-lover.png', 'book', 1, 75),
('Proverbs Wise', 'Complete Proverbs with 80%+ score', 'proverbs-wise.png', 'book', 1, 75),
('Isaiah Scholar', 'Complete Isaiah with 80%+ score', 'isaiah-scholar.png', 'book', 1, 100),
('Gospel Master', 'Complete all 4 Gospels with 80%+ score', 'gospel-master.png', 'category', 4, 300),
('Pauline Expert', 'Complete 10 Pauline epistles with 80%+ score', 'pauline-expert.png', 'category', 10, 250);

-- Create user Bible mastery progress
CREATE TABLE user_bible_mastery (
    user_id INT PRIMARY KEY,
    ot_books_completed INT DEFAULT 0,
    nt_books_completed INT DEFAULT 0,
    total_books_completed INT DEFAULT 0,
    categories_mastered INT DEFAULT 0,
    perfect_scores INT DEFAULT 0,
    current_streak INT DEFAULT 0,
    best_streak INT DEFAULT 0,
    mastery_level ENUM('beginner', 'intermediate', 'advanced', 'expert', 'master') DEFAULT 'beginner',
    last_quiz_at DATETIME,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Bible quiz sharing
CREATE TABLE bible_quiz_shares (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL,
    share_type ENUM('book', 'category', 'custom') NOT NULL,
    share_message TEXT,
    share_url VARCHAR(500),
    social_platform VARCHAR(50),
    views_count INT DEFAULT 0,
    completions_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_shares (user_id, created_at),
    INDEX idx_quiz_shares (quiz_id, share_type)
);