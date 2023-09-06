  const hideShowButtonNavbar = document.getElementById("navbarPasswordBtn");
  const passwordInputNavbar = document.getElementById("navbarPassworInput");
  const hideShowButtonHamburgerMenu = document.getElementById("hamburgerMenuPasswordBtn");
  const passwordInputHamburgerMenu = document.getElementById("hamburgerMenuPassworInput");
  
  hideShowButtonNavbar.onclick = (event) => {
    if (passwordInputNavbar.type === "password") {
      passwordInputNavbar.type = "text";
    } else {
      passwordInputNavbar.type = "password";
    }
  }
  
  hideShowButtonHamburgerMenu.onclick = (event) => {
    if (passwordInputHamburgerMenu.type === "password") {
      passwordInputHamburgerMenu.type = "text";
    } else {
      passwordInputHamburgerMenu.type = "password";
    }
  }