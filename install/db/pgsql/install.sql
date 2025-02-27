CREATE TABLE IF NOT EXISTS claramente_recache_urls (
    "ID" SERIAL PRIMARY KEY,
    "URL" VARCHAR(2083) NOT NULL,
    "URL_HASH" CHAR(32) NOT NULL UNIQUE,
    "STATUS" VARCHAR(16) NOT NULL DEFAULT 'wait',
    "RESPONSE_CODE" INT,
    "REQUEST_TIME_MS" INT,
    "SITE_ID" VARCHAR(12),
    "CREATED_AT" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    "UPDATED_AT" TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX response_code_idx ON claramente_recache_urls ("RESPONSE_CODE");
CREATE INDEX status_idx ON claramente_recache_urls ("STATUS");
CREATE INDEX site_id_idx ON claramente_recache_urls ("SITE_ID");
