(() => {
  for (const element of document.getElementsByTagName('form')) {
    element.addEventListener('submit', (e) => {
      e.preventDefault();
      const check = confirm('Are you sure you want to proceed?');
      if (check) element.submit();
    });
  }
})();
