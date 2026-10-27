(() => {
  const detail = document.querySelector('[data-template-detail]');
  if (!detail) return;

  if (detail.dataset.staticDetails === 'true') {
    const templates = {
      clasica: {
        number: '01',
        name: 'La clásica',
        label: 'Tradición renovada',
        description: 'Una composición editorial con tonos tierra, tipografía con carácter y fotografía enmarcada en arco.',
        audience: 'Para barberías que buscan una imagen familiar y atemporal.',
        features: ['Paleta tierra', 'Imagen en arco'],
      },
      urbana: {
        number: '02',
        name: 'Urbana',
        label: 'Contraste y energía',
        description: 'Un diseño de fondo oscuro, titulares grandes y diagonales con acentos amarillo eléctrico.',
        audience: 'Para negocios que quieren una identidad visual intensa y llamativa.',
        features: ['Fondo oscuro', 'Formas diagonales'],
      },
      minimal: {
        number: '03',
        name: 'Minimal',
        label: 'Simple y natural',
        description: 'Una estructura abierta de fondo claro, tipografía ligera y detalles verdes con formas suaves.',
        audience: 'Para marcas que prefieren una presentación serena y ordenada.',
        features: ['Espacios abiertos', 'Paleta verde'],
      },
    };
    const params = new URLSearchParams(window.location.search);
    const requested = params.get('plantilla');
    const slug = Object.prototype.hasOwnProperty.call(templates, requested) ? requested : 'clasica';
    const template = templates[slug];
    detail.querySelector('[data-template-number]').textContent = `${template.number} / 03 · ${template.label}`;
    detail.querySelector('[data-template-name]').textContent = template.name;
    detail.querySelector('[data-template-description]').textContent = template.description;
    detail.querySelector('[data-template-audience]').textContent = template.audience;
    detail.querySelector('[data-template-demo-number]').textContent = `${template.number} / 03`;
    const features = detail.querySelector('[data-template-features]');
    features.replaceChildren(...template.features.map((feature) => {
      const item = document.createElement('li');
      item.textContent = feature;
      return item;
    }));
    const frame = detail.querySelector('[data-template-frame]');
    frame.src = `${detail.dataset.previewPath}?plantilla=${encodeURIComponent(slug)}`;
    frame.title = `Vista previa de ${template.name}`;
    document.title = `${template.name} · Plantillas Solisweb`;
    const description = document.querySelector('meta[name="description"]');
    if (description) description.content = template.description;
    const socialTitle = document.querySelector('meta[property="og:title"]');
    if (socialTitle) socialTitle.content = `${template.name} · Plantillas Solisweb`;
    const socialDescription = document.querySelector('meta[property="og:description"]');
    if (socialDescription) socialDescription.content = template.description;
  }

  const frame = detail.querySelector('[data-template-frame]');
  if (!frame) return;
  frame.addEventListener('load', () => {
    try {
      const doc = frame.contentDocument;
      const resizeFrame = () => {
        const height = Math.max(doc.documentElement.scrollHeight, doc.body.scrollHeight);
        frame.style.height = `${height}px`;
      };
      resizeFrame();
      frame.contentWindow.addEventListener('resize', resizeFrame);
      doc.querySelectorAll('img').forEach((img) => img.addEventListener('load', resizeFrame, { once: true }));
      if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(resizeFrame);
        observer.observe(doc.documentElement);
        observer.observe(doc.body);
      }
    } catch {
      // Keep the CSS height if the embedded preview is unavailable.
    }
  });
})();
