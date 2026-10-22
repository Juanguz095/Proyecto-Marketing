(() => {
  const requestedTemplate = new URLSearchParams(window.location.search).get('plantilla');
  const availableTemplates = ['clasica', 'urbana', 'minimal'];
  if (document.body.classList.contains('shop-page') && availableTemplates.includes(requestedTemplate)) {
    document.body.classList.remove('template-clasica', 'template-urbana', 'template-minimal');
    document.body.classList.add(`template-${requestedTemplate}`);
    const demoCopy = {
      clasica: { label: 'Estilo clásico', headline: 'Un estilo clásico\nque nunca pasa de moda.', description: 'Cortes clásicos, cuidado de barba y una atención profesional para quienes valoran la tradición.' },
      urbana: { label: 'Estilo urbano', headline: 'Marca tu estilo\ncon cada corte.', description: 'Cortes actuales, detalles precisos y un espacio pensado para expresar tu personalidad.' },
      minimal: { label: 'Estilo minimal', headline: 'Cuidado sencillo,\nbien hecho.', description: 'Un lugar tranquilo para renovar tu estilo, cuidar tu barba y disfrutar de una atención cercana.' },
    }[requestedTemplate];
    const previewLabel = document.querySelector('[data-template-label]');
    if (previewLabel) {
      previewLabel.textContent = `Vista demo: ${demoCopy.label}`;
      const headline = document.querySelector('[data-shop-headline]');
      const description = document.querySelector('[data-shop-description]');
      if (headline) headline.textContent = demoCopy.headline;
      if (description) description.textContent = demoCopy.description;
    }
  }

  const progress = document.querySelector('.scroll-progress');
  const updateProgress = () => {
    if (!progress) return;
    const total = document.documentElement.scrollHeight - window.innerHeight;
    progress.style.width = `${total > 0 ? (window.scrollY / total) * 100 : 0}%`;
  };
  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();

  const revealItems = document.querySelectorAll('.reveal');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ('IntersectionObserver' in window && !reduceMotion) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.13, rootMargin: '0px 0px -24px 0px' });
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  }

  const filterGroup = document.querySelector('[data-template-filters]');
  if (filterGroup) {
    const filterButtons = [...filterGroup.querySelectorAll('[data-template-filter]')];
    const templateCards = [...document.querySelectorAll('[data-template-card]')];
    const status = document.querySelector('[data-template-filter-status]');
    const showTemplates = (filter) => {
      let visibleCount = 0;
      templateCards.forEach((card) => {
        const visible = filter === 'all' || card.dataset.templateCard === filter;
        card.hidden = !visible;
        if (visible) visibleCount += 1;
      });
      filterButtons.forEach((button) => {
        const active = button.dataset.templateFilter === filter;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', String(active));
      });
      if (status) {
        const countLabel = visibleCount === 1 ? 'plantilla' : 'plantillas';
        status.textContent = `Se ${visibleCount === 1 ? 'muestra' : 'muestran'} ${visibleCount} ${countLabel}.`;
      }
    };
    filterButtons.forEach((button) => button.addEventListener('click', () => {
      showTemplates(button.dataset.templateFilter || 'all');
    }));
  }

  const staticContactLink = document.querySelector('[data-contact-whatsapp]');
  if (staticContactLink) {
    const selectedTemplate = new URLSearchParams(window.location.search).get('plantilla');
    const templateNames = { clasica: 'La clásica', urbana: 'Urbana', minimal: 'Minimal' };
    if (Object.prototype.hasOwnProperty.call(templateNames, selectedTemplate)) {
      const label = document.querySelector('[data-contact-template-label]');
      if (label) label.textContent = `Plantilla elegida · ${templateNames[selectedTemplate]}`;
      const message = `Hola OnlyFive, quiero cotizar una página para mi barbería. Me interesa la plantilla ${templateNames[selectedTemplate]}. El nombre de mi barbería es: ____. Necesito: ____.`;
      staticContactLink.href = `https://wa.me/?text=${encodeURIComponent(message)}`;
    }
  }

  const form = document.querySelector('.booking-form[data-availability-endpoint]');
  if (!form) return;

  const serviceInput = form.querySelector('[name="servicio_id"]');
  const barberInput = form.querySelector('[name="barbero_id"]');
  const dateInput = form.querySelector('[name="fecha"]');
  const timeInput = form.querySelector('[name="hora"]');
  const shopInput = form.querySelector('[name="barberia_id"]');
  const status = form.querySelector('[data-availability-status]');
  const submitButton = form.querySelector('button[type="submit"]');
  let activeRequest;

  document.querySelectorAll('[data-select-service]').forEach((link) => {
    link.addEventListener('click', () => {
      const service = link.dataset.selectService;
      if (serviceInput && service) {
        serviceInput.value = service;
        serviceInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });

  const setTimeOptions = (slots) => {
    timeInput.replaceChildren(new Option('Selecciona una hora', ''));
    slots.forEach((slot) => timeInput.add(new Option(slot, slot)));
    timeInput.disabled = slots.length === 0;
    if (submitButton) submitButton.disabled = slots.length === 0;
  };

  const loadAvailability = async () => {
    activeRequest?.abort();
    setTimeOptions([]);
    const serviceId = serviceInput.value;
    const barberId = barberInput.value;
    const date = dateInput.value;
    if (!serviceId || !barberId || !date) {
      status.textContent = 'Elige un servicio, un barbero y una fecha para consultar los horarios disponibles.';
      timeInput.options[0].textContent = 'Primero elige servicio, barbero y fecha';
      return;
    }

    const controller = new AbortController();
    activeRequest = controller;
    status.textContent = 'Buscando horarios disponibles…';
    timeInput.options[0].textContent = 'Cargando horarios…';
    const params = new URLSearchParams({
      barberia_id: shopInput.value,
      servicio_id: serviceId,
      barbero_id: barberId,
      fecha: date,
    });

    try {
      const response = await fetch(`${form.dataset.availabilityEndpoint}?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal: controller.signal,
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'No se pudo consultar la disponibilidad.');
      if (activeRequest !== controller) return;
      const slots = Array.isArray(result.slots) ? result.slots : [];
      setTimeOptions(slots);
      if (slots.length) {
        status.textContent = `Hay ${slots.length} horarios disponibles. Selecciona uno para continuar.`;
      } else {
        status.textContent = 'No hay horarios disponibles para esa fecha. Prueba con otro día.';
        timeInput.options[0].textContent = 'No hay horas disponibles';
      }
    } catch (error) {
      if (error.name === 'AbortError' || activeRequest !== controller) return;
      status.textContent = error.message || 'No se pudo consultar la disponibilidad. Vuelve a intentarlo.';
      timeInput.options[0].textContent = 'No se pudieron cargar las horas';
      timeInput.disabled = true;
      if (submitButton) submitButton.disabled = true;
    }
  };

  [serviceInput, barberInput, dateInput].forEach((input) => {
    input.addEventListener('change', loadAvailability);
  });
})();
