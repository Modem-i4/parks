export const UserRole = {
  SUPER_ADMIN: 'super_admin',
  ADMIN: 'admin',
  NEWS_MANAGER: 'news_manager',
  WORK_MANAGER: 'work_manager',
  EDITOR: 'editor',
  WORKER: 'worker',
  VIEWER: 'viewer',
  GUEST: 'guest',
  DISMISSED: 'dismissed',

  levels: {
    super_admin: 8,
    admin: 7,
    news_manager: 6,
    work_manager: 5,
    editor: 4,
    worker: 3,
    viewer: 2,
    guest: 1,
    dismissed: 0,
  },

  level(role) {
    return this.levels[role] ?? this.levels['guest'];
  },

  fromString(value) {
    if (Object.values(this).includes(value)) {
      return value;
    }
    return this.GUEST;
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
