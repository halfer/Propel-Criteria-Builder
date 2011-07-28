
-----------------------------------------------------------------------------
-- person
-----------------------------------------------------------------------------

DROP TABLE "person" CASCADE;


CREATE TABLE "person"
(
	"id_person" INT8  NOT NULL,
	"name_given" VARCHAR(20),
	"location" VARCHAR(30),
	"gender" CHAR(1),
	"dob" DATE,
	"name_family" VARCHAR(40),
	"enabled" INTEGER,
	PRIMARY KEY ("id_person")
);

COMMENT ON TABLE "person" IS '';


SET search_path TO public;
ALTER TABLE "sf_guard_group_permission" ADD CONSTRAINT "sf_guard_group_permission_FK_1" FOREIGN KEY ("group_id") REFERENCES "sf_guard_group" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_group_permission" ADD CONSTRAINT "sf_guard_group_permission_FK_2" FOREIGN KEY ("permission_id") REFERENCES "sf_guard_permission" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_permission" ADD CONSTRAINT "sf_guard_user_permission_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_permission" ADD CONSTRAINT "sf_guard_user_permission_FK_2" FOREIGN KEY ("permission_id") REFERENCES "sf_guard_permission" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_group" ADD CONSTRAINT "sf_guard_user_group_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_group" ADD CONSTRAINT "sf_guard_user_group_FK_2" FOREIGN KEY ("group_id") REFERENCES "sf_guard_group" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_remember_key" ADD CONSTRAINT "sf_guard_remember_key_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;
