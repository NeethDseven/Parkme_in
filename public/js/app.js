document.addEventListener('DOMContentLoaded', () => {
    const model = new UserModel();
    const controller = new UserController(model);
  });
  