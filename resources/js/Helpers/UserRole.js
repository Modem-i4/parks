export const UserRole = {
  MAIN_ADMIN: 'main_admin',
  ADMIN: 'admin',
  FULL_MANAGER: 'full_manager',
  CONTENT_MANAGER: 'content_manager',
  WORKER: 'worker',
  USER: 'user',
  UNAUTHORIZED: 'unauthorized',

  levels: {
    main_admin: 6,
    admin: 5,
    full_manager: 4,
    content_manager: 3,
    worker: 2,
    user: 1,
    unauthorized: 0,
  },

  level(role) {
    return this.levels[role] ?? this.levels['unauthorized'];
  },

  fromString(value) {
    if (Object.values(this).includes(value)) {
      return value;
    }
    return this.UNAUTHORIZED;
  },

  isOnly(currentRole, roleToCheck) {
    const target = Object.values(this).includes(roleToCheck) ? roleToCheck : this.fromString(roleToCheck);
    return currentRole === target;
  },

  atLeast(currentRole, roleToCheck) {
    const currentLevel = this.level(currentRole);
    const targetRole = Object.values(this).includes(roleToCheck) ? roleToCheck : this.fromString(roleToCheck);
    const targetLevel = this.level(targetRole);
    return currentLevel >= targetLevel;
  }
};
