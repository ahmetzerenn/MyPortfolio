-- Replace default sample projects with Midnight Scour + Colins internship (run once).
-- mysql -u root -p portfolio_db < sql/migration_003_beyond_reach_colins_projects.sql

USE portfolio_db;

UPDATE projects SET
    user_id = 1,
    title = 'Midnight Scour',
    summary = 'Midnight Scour is a horror game developed with Unity as part of a team project. The game focuses on atmosphere, tension, and immersive storytelling.',
    description = 'Midnight Scour is a horror game developed with Unity as part of a team project. The game focuses on atmosphere, tension, and immersive storytelling.\n\nI contribute to the technical side of the project, working on core mechanics and system logic while collaborating with the team to create a cohesive experience.\n\nThe project is currently in development and planned for release on Steam.',
    image_url = '/assets/img/placeholder-project.svg',
    tech_stack = '["Unity", "C#", "Horror", "Steam"]',
    github_url = NULL,
    category_slug = 'general',
    tag = 'Horror · Unity · Steam',
    project_url = NULL,
    display_order = 1,
    is_published = 1
WHERE id = 1;

UPDATE projects SET
    user_id = 1,
    title = 'Colins Internship',
    summary = 'During my internship at Colins, I worked on backend development using ASP.NET. I was involved in building and improving data-driven systems and gained practical experience with SQL databases.',
    description = 'During my internship at Colins, I worked on backend development using ASP.NET. I was involved in building and improving data-driven systems and gained practical experience with SQL databases.\n\nThis experience strengthened my understanding of real-world software development, including system architecture, database design, and performance considerations.',
    image_url = '/assets/img/placeholder-project.svg',
    tech_stack = '["ASP.NET", "SQL", "C#", "Backend"]',
    github_url = NULL,
    category_slug = 'web',
    tag = 'ASP.NET · SQL · Internship',
    project_url = NULL,
    display_order = 2,
    is_published = 1
WHERE id = 2;

DELETE FROM projects WHERE id = 3;

UPDATE users SET full_name = 'Ahmet Zeren' WHERE id = 1;
