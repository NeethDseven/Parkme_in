class UserController {
    constructor(model) {
      this.model = model;
      this.init();
    }
  
    init() {
      document.querySelector('#loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.querySelector('#email').value;
        const password = document.querySelector('#password').value;
        const result = await this.model.login(email, password);
        alert(result);
      });
    }
  }
  