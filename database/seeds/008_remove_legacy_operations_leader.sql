DELETE FROM department_user
WHERE user_id = (
    SELECT id FROM (
        SELECT id
        FROM users
        WHERE email = 'leiter.op@verwaltung.local'
        LIMIT 1
    ) AS legacy_user
);

DELETE FROM users
WHERE email = 'leiter.op@verwaltung.local';
