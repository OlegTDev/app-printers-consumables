export function useUser() {
  const shortUserInfo = (user) => {
    return user.fio || user.name || 'Неизвестен';
  };

  const fullUserInfo = (user) => {
    return {
      name: user.name ?? 'Неизвестен',
      fio: user?.fio || null,
      post: user?.post || null,
      department: user?.department || null,
      telephone: user?.telephone || null,
    };
  };

  return {
    shortUserInfo,
    fullUserInfo,
  };
};
