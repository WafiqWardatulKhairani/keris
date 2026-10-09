-- ============================================================
-- KERIS - Multi Role & Multi Tim Kerja
-- Tanggal: 2026-09-28
-- ============================================================

-- ------------------------------------------------------------
-- 1. Tabel relasi user dengan role
-- Mendukung satu user memiliki lebih dari satu role.
-- ------------------------------------------------------------

CREATE TABLE user_roles (
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,

    CONSTRAINT pk_user_roles
        PRIMARY KEY (user_id, role_id),

    CONSTRAINT fk_user_roles_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    CONSTRAINT fk_user_roles_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
);

-- ------------------------------------------------------------
-- 2. Tabel relasi user dengan tim kerja
-- Mendukung satu user tergabung dalam lebih dari satu tim kerja.
-- ------------------------------------------------------------
CREATE TABLE user_tim_kerja (
    user_id INTEGER NOT NULL,
    id_tim INTEGER NOT NULL,

    CONSTRAINT pk_user_tim_kerja
        PRIMARY KEY (user_id, id_tim),

    CONSTRAINT fk_user_tim_kerja_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    CONSTRAINT fk_user_tim_kerja_tim
        FOREIGN KEY (id_tim)
        REFERENCES tim_kerja(id_tim)
);

-- ------------------------------------------------------------
-- 3. Migrasi relasi role existing
-- Menyalin role lama dari users.role_id ke user_roles.
-- Kolom users.role_id tetap dipertahankan untuk kompatibilitas.
-- ------------------------------------------------------------

INSERT INTO user_roles (user_id, role_id)
SELECT id, role_id
FROM users
WHERE role_id IS NOT NULL;

-- ------------------------------------------------------------
-- 4. Migrasi relasi tim kerja existing
-- Menyalin tim lama dari users.id_tim ke user_tim_kerja.
-- Kolom users.id_tim tetap dipertahankan untuk kompatibilitas.
-- User yang belum memiliki tim (NULL) tidak disalin.
-- ------------------------------------------------------------

INSERT INTO user_tim_kerja (user_id, id_tim)
SELECT id, id_tim
FROM users
WHERE id_tim IS NOT NULL;

-- ------------------------------------------------------------
-- CATATAN
-- ------------------------------------------------------------
-- Tabel user_roles dan user_tim_kerja telah diinisialisasi
-- menggunakan data existing dari users.role_id dan users.id_tim.
--
-- Penambahan role atau tim kerja berikutnya dilakukan dengan
-- menambahkan relasi ke tabel user_roles / user_tim_kerja.
--
-- Contoh:
-- INSERT INTO user_roles (user_id, role_id)
-- VALUES (<user_id>, <role_id>);
--
-- INSERT INTO user_tim_kerja (user_id, id_tim)
-- VALUES (<user_id>, <id_tim>);
--
-- Kolom users.role_id dan users.id_tim tetap dipertahankan
-- sementara untuk kompatibilitas dengan kode existing.

-- ============================================================
-- TEST USER MULTI ROLE
-- Aqilla (user_id = 6)
-- Role: Admin, Operator, Ketua
-- ============================================================

INSERT INTO user_roles (user_id, role_id)
VALUES
    (6, 1),
    (6, 3)
ON CONFLICT (user_id, role_id) DO NOTHING;

-- 4. Tambahan role baru untuk pimpinan
INSERT INTO roles (name, description)
SELECT 'pimpinan', 'Monitoring dashboard dan informasi risiko'
WHERE NOT EXISTS (
    SELECT 1
    FROM roles
    WHERE name = 'pimpinan'
);

-- 5. Tambahan permission dashboard untuk role pimpinan
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'pimpinan'
  AND p.name = 'view_dashboard'
  AND p.module = 'dashboard'
  AND NOT EXISTS (
      SELECT 1
      FROM role_permissions rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );