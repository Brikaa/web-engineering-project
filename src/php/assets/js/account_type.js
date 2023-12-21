(() => {
  const passenger_choice = document.getElementById('passenger-choice');
  const company_choice = document.getElementById('company-choice');
  const passenger_form = document.getElementById('passenger-form');
  const company_form = document.getElementById('company-form');
  passenger_form.classList.add('hidden');
  company_form.classList.add('hidden');
  [
    [passenger_choice, passenger_form, company_choice, company_form],
    [company_choice, company_form, passenger_choice, passenger_form]
  ].forEach(([choice, form, opposite_choice, opposite_form]) => {
    choice.classList.add('selected');
    form.classList.remove('hidden');
    opposite_choice.classList.remove('selected');
    opposite_form.classList.add('hidden');
  });
})();
