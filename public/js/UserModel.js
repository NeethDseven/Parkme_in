class UserModel {
    async login(email, password) {
      const response = await fetch('/backend/controllers/AuthController.php?action=login', {
        method: 'POST',
        body: new URLSearchParams({ email, password }),
      });
      return await response.text();
    }
  }
  