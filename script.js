/**
 * PT Aillena Marine Indonesia - Official Website Scripts
 * Features: Mobile drawer, sticky header, scrollspy nav, smooth scroll, inquiry to WhatsApp generator.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const header = document.getElementById('header');
  const mobileToggle = document.getElementById('mobileToggle');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const drawerClose = document.getElementById('drawerClose');
  const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
  const navLinks = document.querySelectorAll('.nav-link');
  const backToTopBtn = document.getElementById('backToTop');
  const inquiryForm = document.getElementById('inquiryForm');
  const formSuccessMessage = document.getElementById('formSuccessMessage');
  const forwardWaBtn = document.getElementById('forwardWaBtn');

  // 1. Sticky Header Elevation on Scroll
  const handleScroll = () => {
    if (header) {
      if (window.scrollY > 40) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }

    // Back to top button visibility
    if (backToTopBtn) {
      if (window.scrollY > 400) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  // 2. Mobile Drawer Navigation Toggle
  const openDrawer = () => {
    if (mobileDrawer) mobileDrawer.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'true');
  };

  const closeDrawer = () => {
    if (mobileDrawer) mobileDrawer.classList.remove('active');
    document.body.style.overflow = '';
    if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
  };

  if (mobileToggle) {
    mobileToggle.addEventListener('click', openDrawer);
  }

  if (drawerClose) {
    drawerClose.addEventListener('click', closeDrawer);
  }

  // Close drawer if clicking backdrop
  if (mobileDrawer) {
    mobileDrawer.addEventListener('click', (e) => {
      if (e.target === mobileDrawer) {
        closeDrawer();
      }
    });
  }

  // Auto-close drawer on link click
  mobileNavLinks.forEach((link) => {
    link.addEventListener('click', () => {
      closeDrawer();
    });
  });

  // 3. Back to Top Click
  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // 4. Scrollspy for Active Navigation Link
  const sections = document.querySelectorAll('section[id]');
  const observerOptions = {
    root: null,
    rootMargin: '-20% 0px -70% 0px',
    threshold: 0
  };

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id');
        navLinks.forEach((link) => {
          if (link.getAttribute('href') === `#${id}`) {
            link.classList.add('active');
          } else {
            link.classList.remove('active');
          }
        });

        // Also update subnav pills on about.html
        const subnavBtns = document.querySelectorAll('.subnav-btn');
        if (subnavBtns.length > 0) {
          subnavBtns.forEach((btn) => {
            if (btn.getAttribute('href') === `#${id}`) {
              btn.classList.add('active');
              // Auto-scroll pill into view on mobile if needed
              btn.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
            } else {
              btn.classList.remove('active');
            }
          });
        }
      }
    });
  }, observerOptions);

  sections.forEach((section) => {
    sectionObserver.observe(section);
  });

  // 4b. Subnav Horizontal Scroll Slider & Arrow Controls (about, services, workflow)
  const initSubnavSlider = () => {
    const subnavPills = document.getElementById('subnavPills');
    const arrowLeft = document.getElementById('subnavArrowLeft');
    const arrowRight = document.getElementById('subnavArrowRight');

    if (!subnavPills) return;

    const updateArrows = () => {
      const scrollLeft = subnavPills.scrollLeft;
      const maxScroll = subnavPills.scrollWidth - subnavPills.clientWidth;

      if (maxScroll <= 4) {
        if (arrowLeft) arrowLeft.style.display = 'none';
        if (arrowRight) arrowRight.style.display = 'none';
        return;
      }

      if (arrowLeft) {
        arrowLeft.style.display = 'flex';
        if (scrollLeft <= 4) {
          arrowLeft.classList.add('disabled');
        } else {
          arrowLeft.classList.remove('disabled');
        }
      }

      if (arrowRight) {
        arrowRight.style.display = 'flex';
        if (scrollLeft >= maxScroll - 4) {
          arrowRight.classList.add('disabled');
        } else {
          arrowRight.classList.remove('disabled');
        }
      }
    };

    if (arrowLeft) {
      arrowLeft.addEventListener('click', (e) => {
        e.preventDefault();
        subnavPills.scrollBy({ left: -260, behavior: 'smooth' });
      });
    }

    if (arrowRight) {
      arrowRight.addEventListener('click', (e) => {
        e.preventDefault();
        subnavPills.scrollBy({ left: 260, behavior: 'smooth' });
      });
    }

    subnavPills.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows, { passive: true });
    setTimeout(updateArrows, 150);
    window.addEventListener('load', updateArrows);
  };
  initSubnavSlider();

  // 5. Team Personnel Category Filtering (about.html)
  const filterBtns = document.querySelectorAll('.team-filter-btn');
  const personCards = document.querySelectorAll('.person-card');

  if (filterBtns.length > 0 && personCards.length > 0) {
    filterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        // Toggle active button state
        filterBtns.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');

        const filterValue = btn.getAttribute('data-filter');

        personCards.forEach((card) => {
          const category = card.getAttribute('data-category');
          if (filterValue === 'all' || category === filterValue) {
            card.style.display = 'flex';
            card.style.animation = 'fadeInCard 0.4s ease forwards';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // 6. Gallery Filtering & Lightbox Modal (gallery.html & index.html)
  const galleryFilterBtns = document.querySelectorAll('.gallery-filter-btn');
  const galleryCards = document.querySelectorAll('.gallery-card');
  const galleryModal = document.getElementById('galleryModal');
  const modalImg = document.getElementById('galleryModalImg');
  const modalTitle = document.getElementById('galleryModalTitle');
  const modalDesc = document.getElementById('galleryModalDesc');
  const modalClose = document.getElementById('galleryModalClose');

  if (galleryFilterBtns.length > 0 && galleryCards.length > 0) {
    galleryFilterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        galleryFilterBtns.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.getAttribute('data-filter');
        galleryCards.forEach((card) => {
          const category = card.getAttribute('data-category') || '';
          const catList = category.split(/\s+/);
          if (filter === 'all' || catList.includes(filter) || category === filter) {
            card.style.display = 'flex';
            card.style.animation = 'fadeInModal 0.4s ease forwards';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // Lightbox modal trigger
  if (galleryCards.length > 0 && galleryModal) {
    galleryCards.forEach((card) => {
      card.addEventListener('click', () => {
        const img = card.querySelector('img');
        const title = card.querySelector('.gallery-card-body h4');
        const desc = card.querySelector('.gallery-card-body p');

        if (img && modalImg) modalImg.src = img.src;
        if (title && modalTitle) modalTitle.textContent = title.textContent;
        if (desc && modalDesc) modalDesc.textContent = desc.textContent;

        galleryModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    });

    if (modalClose) {
      modalClose.addEventListener('click', () => {
        galleryModal.classList.remove('active');
        document.body.style.overflow = '';
      });
    }

    galleryModal.addEventListener('click', (e) => {
      if (e.target === galleryModal) {
        galleryModal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && galleryModal.classList.contains('active')) {
        galleryModal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  }

  // 7. Dynamic Gallery Loader (CMS Integration with Fallback)
  const loadDynamicGallery = () => {
    const galleryGrid = document.getElementById('galleryGrid');
    if (!galleryGrid) return;

    fetch('api/get_gallery.php')
      .then(res => res.json())
      .then(res => {
        if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
          // Map category to filter class
          const catClassMap = (cat) => {
            const c = (cat || '').toLowerCase();
            if (c.includes('engine') || c.includes('mesin')) return 'engine workshop';
            if (c.includes('body') || c.includes('bodi') || c.includes('cat')) return 'body workshop';
            if (c.includes('dock') || c.includes('rel') || c.includes('slipway')) return 'docking';
            if (c.includes('sea') || c.includes('trial') || c.includes('uji')) return 'seatrial';
            return 'workshop';
          };

          // Re-render gallery grid
          galleryGrid.innerHTML = '';
          res.data.forEach(item => {
            const card = document.createElement('div');
            card.className = 'gallery-card';
            card.setAttribute('data-category', catClassMap(item.category));
            card.innerHTML = `
              <div class="gallery-img-wrap">
                <img src="${item.image_url}" alt="${item.title}" loading="lazy">
                <span class="gallery-badge"><i class="fa-solid fa-camera"></i> ${item.category}</span>
                <span class="gallery-zoom-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
              </div>
              <div class="gallery-card-body">
                <h4>${item.title}</h4>
                <p>${item.description || ''}</p>
                <div class="gallery-card-meta">
                  <span><i class="fa-solid fa-location-dot gold-text"></i> ${item.location || 'Workshop Memey Marine'}</span>
                  <span><i class="fa-solid fa-tag gold-text"></i> ${item.tags || 'Dokumentasi'}</span>
                </div>
              </div>
            `;
            // Attach lightbox click
            card.addEventListener('click', () => {
              if (galleryModal && modalImg && modalTitle && modalDesc) {
                modalImg.src = item.image_url;
                modalTitle.textContent = item.title;
                modalDesc.textContent = item.description || '';
                galleryModal.classList.add('active');
                document.body.style.overflow = 'hidden';
              }
            });
            galleryGrid.appendChild(card);
          });
        }
      })
      .catch(() => {
        // Fallback: Static HTML remains unchanged
      });
  };
  loadDynamicGallery();

  // 8. Inquiry Form Handling (Saves to CMS Database & Direct WhatsApp)
  if (inquiryForm) {
    inquiryForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const name = document.getElementById('clientName').value.trim();
      const org = document.getElementById('clientOrg').value.trim() || '-';
      const phone = document.getElementById('clientPhone').value.trim();
      const service = document.getElementById('serviceType').value;
      const boat = document.getElementById('boatDetails').value.trim() || '-';
      const message = document.getElementById('message').value.trim();

      if (!name || !phone || !service || !message) {
        alert('Mohon lengkapi semua kolom yang wajib diisi.');
        return;
      }

      // Default WhatsApp text format
      const waText = 
`*PERMINTAAN KONSULTASI / LAYANAN*
*PT AILLENA MARINE INDONESIA*
-----------------------------------
*Nama:* ${name}
*Instansi/Perusahaan:* ${org}
*No. Telepon/WA:* ${phone}
*Layanan yang Dibutuhkan:* ${service}
*Info Kapal/Mesin:* ${boat}
*Rincian Kebutuhan:*
${message}
-----------------------------------
_Dikirim via Formulir Website Aillena Marine_`;

      const fallbackWaUrl = `https://wa.me/6282323212323?text=${encodeURIComponent(waText)}`;

      const submitBtn = inquiryForm.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan Permintaan...';
      }

      const formData = new FormData();
      formData.append('clientName', name);
      formData.append('clientOrg', org);
      formData.append('clientPhone', phone);
      formData.append('serviceType', service);
      formData.append('boatDetails', boat);
      formData.append('message', message);

      // Post to CMS API with fallback
      fetch('api/submit_inquiry.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(result => {
        let finalWaUrl = fallbackWaUrl;
        if (result && result.success && result.wa_url) {
          finalWaUrl = result.wa_url;
        }

        if (forwardWaBtn) {
          forwardWaBtn.setAttribute('href', finalWaUrl);
        }

        if (formSuccessMessage) {
          formSuccessMessage.style.display = 'flex';
          formSuccessMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        window.open(finalWaUrl, '_blank');
        inquiryForm.reset();
      })
      .catch(err => {
        console.warn('CMS API offline, continuing with WhatsApp direct link:', err);
        if (forwardWaBtn) {
          forwardWaBtn.setAttribute('href', fallbackWaUrl);
        }

        if (formSuccessMessage) {
          formSuccessMessage.style.display = 'flex';
          formSuccessMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        window.open(fallbackWaUrl, '_blank');
        inquiryForm.reset();
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      });
    });
  }

  // ==========================================================================
  // 8. EXECUTIVE ANIMATION SUITE - PT AILLENA MARINE INDONESIA
  // ==========================================================================

  // A. Scroll Reading Progress Bar
  const initScrollProgress = () => {
    let progressBar = document.getElementById('scrollProgressBar');
    if (!progressBar) {
      progressBar = document.createElement('div');
      progressBar.id = 'scrollProgressBar';
      progressBar.className = 'scroll-progress-bar';
      progressBar.setAttribute('aria-hidden', 'true');
      document.body.prepend(progressBar);
    }

    const updateProgress = () => {
      const scrollTop = window.scrollY || document.documentElement.scrollTop;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
    };

    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();
  };
  initScrollProgress();

  // B. Automatic Stagger & Scroll Reveal System (IntersectionObserver)
  const initScrollReveal = () => {
    // 1. Automatically assign reveal classes to core components across all pages
    const revealRules = [
      { selector: '.section-header', class: 'reveal-up' },
      { selector: '.services-grid > .service-card', class: 'reveal-up', stagger: 120 },
      { selector: '.workflow-steps > .step-card', class: 'reveal-up', stagger: 100 },
      { selector: '.gallery-grid > .gallery-card', class: 'reveal-up', stagger: 100 },
      { selector: '.clients-grid > .client-badge-item', class: 'reveal-up', stagger: 100 },
      { selector: '.partner-logos-grid > .partner-card', class: 'reveal-scale', stagger: 80 },
      { selector: '.hero-stats > .stat-card', class: 'reveal-up', stagger: 120 },
      { selector: '.hero-quick-stats > .quick-stat-box', class: 'reveal-up', stagger: 100 },
      { selector: '.about-grid > div', class: 'reveal-up', stagger: 150 },
      { selector: '.contact-grid > div', class: 'reveal-up', stagger: 150 },
      { selector: '.about-subpage-cta-banner', class: 'reveal-up' },
      { selector: '.team-personnel-grid > .person-card', class: 'reveal-up', stagger: 90 },
      { selector: '.service-detail-block', class: 'reveal-up' },
      { selector: '.workflow-stage-detail', class: 'reveal-up' },
      { selector: '.core-values-grid > .core-value-card', class: 'reveal-up', stagger: 90 },
      { selector: '.sla-grid > .sla-card', class: 'reveal-up', stagger: 90 },
      { selector: '.map-container-card', class: 'reveal-up' },
      { selector: '.faq-card', class: 'reveal-up', stagger: 80 },
      { selector: '.legal-badge-card', class: 'reveal-up' },
      { selector: '.team-overview-card', class: 'reveal-up' },
      { selector: '.workshop-card', class: 'reveal-up' }
    ];

    revealRules.forEach((rule) => {
      const items = document.querySelectorAll(rule.selector);
      items.forEach((item, index) => {
        if (!item.classList.contains('reveal-on-scroll')) {
          item.classList.add('reveal-on-scroll', rule.class);
          if (rule.stagger) {
            const delay = (index % 6) * rule.stagger;
            if (delay > 0) {
              item.style.transitionDelay = `${delay}ms`;
            }
          }
        }
      });
    });

    // 2. Set up IntersectionObserver with smooth threshold
    const revealElements = document.querySelectorAll('.reveal-on-scroll');
    if (!('IntersectionObserver' in window)) {
      revealElements.forEach(el => el.classList.add('is-revealed'));
      return;
    }

    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          observer.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      rootMargin: '0px 0px -45px 0px',
      threshold: 0.12
    });

    revealElements.forEach(el => revealObserver.observe(el));
  };
  initScrollReveal();

  // C. Animated Numerical Counters (Count-Up Effect)
  const initCounterAnimation = () => {
    // Select elements that typically display statistical numbers
    const statElements = document.querySelectorAll(
      '.quick-stat-num, .stat-number, .stat-card h4, .stage-metric-badge strong, .counter-number'
    );

    const parseNumber = (text) => {
      // Find the first integer or decimal number in the text
      const match = text.match(/([0-9]+([.,][0-9]+)?)/);
      if (!match) return null;
      const numStr = match[1].replace(',', '.');
      const val = parseFloat(numStr);
      if (isNaN(val) || val <= 0) return null;
      
      const startIndex = match.index;
      const prefix = text.substring(0, startIndex);
      const suffix = text.substring(startIndex + match[0].length);
      const isInteger = !numStr.includes('.');

      return {
        target: val,
        prefix,
        suffix,
        isInteger
      };
    };

    const animateNumber = (element, parsed) => {
      const duration = 1600; // ms
      const startTime = performance.now();

      const update = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // easeOutCubic
        const ease = 1 - Math.pow(1 - progress, 3);
        const currentVal = ease * parsed.target;

        const formatted = parsed.isInteger 
          ? Math.floor(currentVal) 
          : currentVal.toFixed(1);

        element.textContent = `${parsed.prefix}${formatted}${parsed.suffix}`;

        if (progress < 1) {
          requestAnimationFrame(update);
        } else {
          element.textContent = `${parsed.prefix}${parsed.isInteger ? parsed.target : parsed.target.toFixed(1)}${parsed.suffix}`;
        }
      };

      requestAnimationFrame(update);
    };

    if (!('IntersectionObserver' in window)) return;

    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const parsed = parseNumber(el.textContent.trim());
          if (parsed && parsed.target > 1) {
            el.classList.add('counter-number');
            animateNumber(el, parsed);
          }
          observer.unobserve(el);
        }
      });
    }, {
      threshold: 0.25
    });

    statElements.forEach(el => counterObserver.observe(el));
  };
  initCounterAnimation();

  // D. Hero Section Entrance Stagger
  const initHeroEntrance = () => {
    const heroElements = document.querySelectorAll(
      '.hero-badge, .hero-title, .hero-description, .hero-actions, .hero-stats'
    );
    heroElements.forEach((el, index) => {
      el.classList.add('reveal-on-scroll', 'reveal-up');
      el.style.transitionDelay = `${index * 120}ms`;
      requestAnimationFrame(() => {
        el.classList.add('is-revealed');
      });
    });
  };
  initHeroEntrance();

  // E. Subtle 3D Card Interactive Lighting on Desktop
  const initCardMicroInteraction = () => {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    const interactiveCards = document.querySelectorAll('.service-card, .stat-card, .step-card');
    interactiveCards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        const rotateX = (-y / rect.height) * 4; // max 4 deg
        const rotateY = (x / rect.width) * 4;  // max 4 deg
        card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-6px)`;
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    });
  };
  initCardMicroInteraction();
});


