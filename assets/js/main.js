/* ============================================================
   MusicArtPlus — интерфейсная логика
   Без внешних зависимостей. Всё анимируется через CSS,
   JS только переключает классы (минимум работы для процессора).
   ============================================================ */
(function () {
  'use strict';

  /* --- Единая точка настройки ссылок в CRM «Мой класс» --------------
     После подключения CRM достаточно поменять значения здесь
     (или подставить виджет записи вместо перехода по ссылке).      */
  var CRM = {
    base: 'https://app.moyklass.com/',
    // ссылка «Записаться» для конкретного педагога
    forTeacher: function (slug) {
      return this.base + (slug ? '?teacher=' + encodeURIComponent(slug) : '');
    }
  };
  window.MAP_CRM = CRM;

  var $  = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- 1. Прелоадер ---------- */
  var pre = $('.preloader');
  if (pre) {
    window.addEventListener('load', function () {
      setTimeout(function () { pre.classList.add('is-hidden'); }, 260);
    });
    setTimeout(function () { pre.classList.add('is-hidden'); }, 2600); // страховка
  }

  /* ---------- 2. Шапка: компактный режим при скролле ---------- */
  var header = $('.header');
  if (header) {
    var lastStuck = null;
    var onScroll = function () {
      var stuck = window.scrollY > 24;
      if (stuck !== lastStuck) { header.classList.toggle('is-stuck', stuck); lastStuck = stuck; }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------- 3. Мобильное меню ---------- */
  var burger = $('.burger');
  if (burger) {
    burger.addEventListener('click', function () {
      var open = document.body.classList.toggle('menu-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });
    $$('.mobile-menu a').forEach(function (a) {
      a.addEventListener('click', function () {
        document.body.classList.remove('menu-open');
        document.body.style.overflow = '';
        burger.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ---------- 4. Появление блоков при скролле ---------- */
  var revealables = $$('.reveal, .draw, .brush');
  if (revealables.length) {
    // длина линий для «прорисовки»
    $$('.draw').forEach(function (svg) {
      $$('path, line, circle, ellipse', svg).forEach(function (p) {
        var len = 0;
        try { len = Math.ceil(p.getTotalLength()); } catch (e) { len = 1200; }
        p.style.setProperty('--len', len);
      });
    });

    if ('IntersectionObserver' in window && !reduced) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
        });
      }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
      revealables.forEach(function (el) { io.observe(el); });
    } else {
      revealables.forEach(function (el) { el.classList.add('is-visible'); });
    }
  }

  /* ---------- 5. Заголовок: слова выезжают из-под маски ---------- */
  var title = $('[data-reveal-title]');
  if (title && !reduced) {
    var words = title.textContent.trim().split(/\s+/);
    var html = '';
    for (var wi = 0; wi < words.length; wi++) {
      html += '<span class="w"><span class="w__i" style="animation-delay:' +
              (0.28 + wi * 0.085).toFixed(3) + 's">' + words[wi] + '</span></span>';
      if (wi < words.length - 1) html += ' ';
    }
    title.innerHTML = html;
  }

  /* ---------- 6. Слайдер первого экрана ---------- */
  var hero = $('.hero');
  if (hero) {
    var slides = $$('.hero__slide', hero);
    var dotsBox = $('.hero__dots', hero);
    if (slides.length > 1) {
      var cur = 0, timer = null;
      var dots = slides.map(function (_, idx) {
        var b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Слайд ' + (idx + 1));
        if (idx === 0) b.classList.add('is-active');
        b.addEventListener('click', function () { go(idx); restart(); });
        if (dotsBox) dotsBox.appendChild(b);
        return b;
      });
      var go = function (n) {
        slides[cur].classList.remove('is-active');
        dots[cur].classList.remove('is-active');
        cur = (n + slides.length) % slides.length;
        slides[cur].classList.add('is-active');
        dots[cur].classList.add('is-active');
      };
      var restart = function () {
        clearInterval(timer);
        if (!reduced) timer = setInterval(function () { go(cur + 1); }, 6000);
      };
      restart();
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) clearInterval(timer); else restart();
      });
    }
  }

  /* ---------- 7. Слайдеры на Swiper ---------- */
  if (window.Swiper) {
    // наборы настроек: имя из data-swiper-preset
    var PRESETS = {
      // всегда слайдер: 1 → 2 → 3 карточки
      cards: {
        slidesPerView: 1.1, spaceBetween: 16,
        breakpoints: {
          560:  { slidesPerView: 1.6, spaceBetween: 20 },
          760:  { slidesPerView: 2,   spaceBetween: 22 },
          1080: { slidesPerView: 3,   spaceBetween: 26 }
        }
      },
      // на десктопе — обычная сетка, слайдер включается только на узких экранах
      gridMobile: {
        slidesPerView: 1.1, spaceBetween: 16,
        breakpoints: { 560: { slidesPerView: 1.6, spaceBetween: 20 } }
      }
    };

    var merge = function (a, b) {
      var out = {}, k;
      for (k in a) if (Object.prototype.hasOwnProperty.call(a, k)) out[k] = a[k];
      for (k in b) if (Object.prototype.hasOwnProperty.call(b, k)) out[k] = b[k];
      return out;
    };
    var clone = function (o) { return JSON.parse(JSON.stringify(o)); };

    $$('[data-swiper]').forEach(function (root) {
      var el = $('.swiper', root);
      if (!el) return;

      var name   = root.getAttribute('data-swiper');
      var preset = PRESETS[root.getAttribute('data-swiper-preset') || 'cards'] || PRESETS.cards;
      var nav    = $('[data-swiper-nav="' + name + '"]');

      var build = function () {
        var opts = merge(clone(preset), {
          speed: 520,
          watchOverflow: true,
          grabCursor: true,
          a11y: {
            prevSlideMessage: 'Предыдущий слайд',
            nextSlideMessage: 'Следующий слайд',
            paginationBulletMessage: 'Перейти к слайду {{index}}'
          },
          keyboard: { enabled: true, onlyInViewport: true },
          pagination: { el: $('.swiper-pagination', root), clickable: true }
        });
        if (nav) {
          opts.navigation = {
            prevEl: $('[data-c-prev]', nav),
            nextEl: $('[data-c-next]', nav)
          };
        }
        return new Swiper(el, opts);
      };

      // «сетка на десктопе → слайдер на мобильном»
      if (root.classList.contains('slider--grid')) {
        var mq = window.matchMedia('(max-width: 760px)');
        var inst = null;
        var sync = function () {
          if (mq.matches && !inst) inst = build();
          else if (!mq.matches && inst) { inst.destroy(true, true); inst = null; }
        };
        sync();
        if (mq.addEventListener) mq.addEventListener('change', sync);
        else if (mq.addListener) mq.addListener(sync);
      } else {
        build();
      }
    });
  }

  /* ---------- 8. Аккордеон FAQ ---------- */
  $$('.faq').forEach(function (faq) {
    $$('.faq__q', faq).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.faq__item');
        var panel = $('.faq__a', item);
        var open = item.classList.contains('is-open');

        $$('.faq__item.is-open', faq).forEach(function (other) {
          other.classList.remove('is-open');
          $('.faq__a', other).style.maxHeight = '';
          $('.faq__q', other).setAttribute('aria-expanded', 'false');
        });

        if (!open) {
          item.classList.add('is-open');
          panel.style.maxHeight = panel.scrollHeight + 'px';
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  });

  /* ---------- 9. Универсальные модальные окна ---------- */
  var openModal = function (m) {
    m.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    var c = $('.modal__close', m);
    if (c) c.focus({ preventScroll: true });
  };
  var closeModal = function (m) {
    m.classList.remove('is-open');
    document.body.style.overflow = '';
    var v = $('.modal__video', m);
    if (v) v.innerHTML = ''; // остановить воспроизведение
  };
  $$('.modal').forEach(function (m) {
    $$('[data-close], .modal__backdrop', m).forEach(function (el) {
      el.addEventListener('click', function () { closeModal(m); });
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') $$('.modal.is-open').forEach(closeModal);
  });

  /* ---------- 10. Видео (Rutube / локальные файлы) ---------- */
  var videoModal = $('#video-modal');
  if (videoModal) {
    var stage = $('.modal__video', videoModal);
    var vFoot = $('.v-foot', videoModal);

    $$('[data-video]').forEach(function (card) {
      card.addEventListener('click', function (e) {
        e.preventDefault();
        var src  = card.getAttribute('data-video');
        var type = card.getAttribute('data-video-type') || 'iframe';
        var page = card.getAttribute('data-video-page') || '';
        var host = card.getAttribute('data-video-host') || 'Rutube';

        // Ролики, снятые на телефон, вертикальные: в окне 16:9 от них остаётся
        // узкая полоска между двумя чёрными полями.
        var box = videoModal.querySelector('.modal__box--video');
        if (box) {
          box.classList.toggle('is-vertical', card.getAttribute('data-video-ratio') === 'vertical');
        }

        stage.innerHTML =
          '<div class="v-load"><span class="v-load__spin"></span><span>Загружаем видео…</span></div>' +
          (type === 'file'
            ? '<video src="' + src + '" controls autoplay playsinline></video>'
            : '<iframe src="' + src + '" allow="clipboard-write; autoplay; fullscreen" ' +
              'webkitAllowFullScreen mozallowfullscreen allowFullScreen frameborder="0" ' +
              'title="Видео MusicArtPlus"></iframe>');

        var frame = stage.querySelector('iframe, video');
        var loader = stage.querySelector('.v-load');
        var ready = function () {
          if (!frame) return;
          frame.classList.add('is-ready');
          if (loader) setTimeout(function () { if (loader.parentNode) loader.parentNode.removeChild(loader); }, 500);
        };
        if (frame) {
          frame.addEventListener('load', function () { setTimeout(ready, 400); });
          frame.addEventListener('loadeddata', ready);
          setTimeout(ready, 4000); // страховка, если событие не придёт
        }

        // запасная ссылка на первоисточник
        if (vFoot) {
          if (page) {
            vFoot.innerHTML = '<span>Видео не запускается?</span>' +
              '<a href="' + page + '" target="_blank" rel="noopener">Открыть на ' + host +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
              'stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9"/>' +
              '<path d="M18 14v4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h4"/></svg></a>';
            vFoot.style.display = '';
          } else {
            vFoot.style.display = 'none';
          }
        }

        openModal(videoModal);
      });
    });
  }

  /* ---------- 11. Карточка педагога ---------- */
  var tModal = $('#teacher-modal');
  if (tModal) {
    var slot = $('[data-teacher-slot]', tModal);
    var esc = function (s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    };

    var render = function (d) {
      var items = [];
      try { items = JSON.parse(d.facts || '[]'); } catch (e) {}
      var days = [];
      try { days = JSON.parse(d.schedule || '[]'); } catch (e) {}

      var facts = items.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('');
      var sched = days.map(function (x) {
        return '<div class="tm__day' + (x.time ? '' : ' tm__day--off') + '">' +
               '<b>' + esc(x.day) + '</b><span>' + esc(x.time || 'выходной') + '</span></div>';
      }).join('');

      return '' +
        '<div class="tm">' +
          '<div class="tm__media"><img src="' + esc(d.photo) + '" alt="' + esc(d.name) + '" loading="lazy"></div>' +
          '<div class="tm__body">' +
            '<span class="chip">' + esc(d.subject || 'Педагог') + '</span>' +
            '<h3 class="tm__name">' + esc(d.name) + '</h3>' +
            '<div class="tm__role">' + esc(d.role || '') + '</div>' +
            (d.bio ? '<p style="margin-top:16px">' + esc(d.bio) + '</p>' : '') +
            (facts ? '<ul class="tm__list">' + facts + '</ul>' : '') +
            (sched ?
              '<div class="tm__schedule">' +
                '<h4><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>Расписание педагога</h4>' +
                '<div class="tm__days">' + sched + '</div>' +
                '<p class="tm__note">Актуальные свободные слоты и запись — в системе «Мой класс».</p>' +
              '</div>' : '') +
            '<div class="tm__actions">' +
              '<a class="btn btn--gold" href="' + CRM.forTeacher(d.slug) + '"' +
                ' data-crm="' + esc(d.slug) + '" data-crm-name="' + esc(d.name) + '"' +
                ' data-crm-photo="' + esc(d.photo) + '">Записаться на урок</a>' +
              '<a class="btn btn--ghost" href="tel:+79031025111">' +
                '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 3h3l1.5 4-2 1.5a12.2 12.2 0 006.4 6.4l1.5-2 4 1.5v3a2 2 0 01-2.2 2A17.2 17.2 0 014.6 5.2 2 2 0 016.6 3z"/></svg>' +
                '+7 903 102-51-11</a>' +
            '</div>' +
          '</div>' +
        '</div>';
    };

    $$('[data-teacher]').forEach(function (card) {
      var open = function (e) {
        e.preventDefault();
        slot.innerHTML = render({
          slug:     card.getAttribute('data-teacher'),
          name:     card.getAttribute('data-name'),
          role:     card.getAttribute('data-role'),
          subject:  card.getAttribute('data-subject'),
          photo:    card.getAttribute('data-photo'),
          bio:      card.getAttribute('data-bio'),
          facts:    card.getAttribute('data-facts'),
          schedule: card.getAttribute('data-schedule')
        });
        openModal(tModal);
      };
      $$('[data-teacher-open]', card).forEach(function (t) { t.addEventListener('click', open); });
    });
  }

  /* ---------- 12. Кнопки «Записаться» → модальное окно с формой ----------
     href остаётся ссылкой в CRM: если скрипт не сработает, кнопка всё равно
     приведёт пользователя в «Мой класс». */
  var bkModal = $('#booking-modal');

  var setCrmHref = function (el) {
    var slug = el.getAttribute('data-crm');
    el.setAttribute('href', CRM.forTeacher(slug === 'true' ? '' : slug));
  };
  $$('[data-crm]').forEach(setCrmHref);

  if (bkModal) {
    var bkCtx   = $('[data-bk-ctx]', bkModal);
    var bkPhoto = $('[data-bk-photo]', bkModal);
    var bkLabel = $('[data-bk-label]', bkModal);
    var bkName  = $('[data-bk-name]', bkModal);
    var bkInput = $('[data-bk-input]', bkModal);
    var bkDir   = $('[data-bk-dir]', bkModal);
    var bkCrm   = $('[data-bk-crm]', bkModal);
    var bkForm  = $('#form-booking', bkModal);

    var openBooking = function (trigger) {
      var slug    = trigger.getAttribute('data-crm');
      var card    = trigger.closest ? trigger.closest('[data-teacher]') : null;
      var name    = trigger.getAttribute('data-crm-name')  || (card && card.getAttribute('data-name'))  || '';
      var photo   = trigger.getAttribute('data-crm-photo') || (card && card.getAttribute('data-photo')) || '';
      var subject = trigger.getAttribute('data-crm-subject') || '';

      // сброс предыдущего состояния
      bkCtx.classList.remove('is-shown');
      bkPhoto.removeAttribute('src');
      bkInput.value = '';
      bkForm.classList.remove('is-sent');
      $$('.field.has-error', bkForm).forEach(function (f) { f.classList.remove('has-error'); });

      if (name) {
        bkLabel.textContent = 'Педагог';
        bkName.textContent = name;
        if (photo) bkPhoto.setAttribute('src', photo.indexOf('/') > -1 ? photo : 'assets/img/teachers/' + photo);
        bkInput.value = name;
        bkCtx.classList.add('is-shown');
      } else if (subject) {
        bkLabel.textContent = 'Направление';
        bkName.textContent = subject;
        bkCtx.classList.add('is-shown');
      }

      // если пришли от педагога — подставим его направление
      if (!subject && card) {
        var raw = card.getAttribute('data-subject') || '';
        subject = raw.split(' · ')[0];
      }

      if (subject && bkDir) {
        var low = subject.toLowerCase(), hit = -1;
        for (var i = 0; i < bkDir.options.length; i++) {
          var v = bkDir.options[i].value.toLowerCase();
          if (v === low) { hit = i; break; }
          if (hit < 0 && (v.indexOf(low) === 0 || low.indexOf(v.split(' ')[0]) === 0)) hit = i;
        }
        bkDir.selectedIndex = hit > -1 ? hit : bkDir.options.length - 1;
      } else if (bkDir) {
        bkDir.selectedIndex = bkDir.options.length - 1;
      }

      if (bkCrm) bkCrm.setAttribute('href', CRM.forTeacher(slug === 'true' ? '' : slug));

      // если открыто другое окно — закрываем его
      $$('.modal.is-open').forEach(function (m) { if (m !== bkModal) closeModal(m); });
      openModal(bkModal);
      var first = $('#bk-name', bkModal);
      if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 320);
    };

    // делегирование: работает и для кнопок, созданных скриптом
    document.addEventListener('click', function (e) {
      var t = e.target.closest ? e.target.closest('[data-crm]') : null;
      if (!t) return;
      e.preventDefault();
      setCrmHref(t);
      openBooking(t);
    });
  }

  /* ---------- 13. Маска телефона +7 ---------- */
  $$('input[type="tel"]').forEach(function (input) {
    var format = function (v) {
      var d = v.replace(/\D/g, '');
      if (d.charAt(0) === '8') d = '7' + d.slice(1);
      if (d.charAt(0) !== '7') d = '7' + d;
      d = d.slice(0, 11);
      var out = '+7';
      if (d.length > 1) out += ' (' + d.slice(1, 4);
      if (d.length >= 5) out += ') ' + d.slice(4, 7);
      if (d.length >= 8) out += '-' + d.slice(7, 9);
      if (d.length >= 10) out += '-' + d.slice(9, 11);
      return out;
    };
    var apply = function () { input.value = format(input.value); };
    input.addEventListener('focus', function () { if (!input.value) input.value = '+7 ('; });
    input.addEventListener('input', apply);
    input.addEventListener('blur', function () { if (input.value.replace(/\D/g, '').length < 2) input.value = ''; });
  });

  /* ---------- 14. Формы ---------- */
  $$('form[data-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = true;

      $$('.field', form).forEach(function (f) {
        var inp = $('input, textarea', f);
        if (!inp || !inp.required) return;
        var val = inp.value.trim();
        var bad = !val || (inp.type === 'tel' && val.replace(/\D/g, '').length < 11);
        f.classList.toggle('has-error', bad);
        if (bad) ok = false;
      });

      var agree = $('input[type="checkbox"][required]', form);
      if (agree && !agree.checked) { ok = false; agree.focus(); }
      if (!ok) return;

      /* Здесь будет отправка заявки в CRM «Мой класс» / на почту центра.
         На этапе вёрстки — визуальное подтверждение. */
      form.classList.add('is-sent');
      var btn = $('button[type="submit"]', form);
      if (btn) { btn.disabled = true; btn.textContent = 'Заявка отправлена'; }
      setTimeout(function () {
        form.reset();
        form.classList.remove('is-sent');
        if (btn) { btn.disabled = false; btn.textContent = btn.getAttribute('data-label') || 'Отправить'; }
      }, 5000);
    });

    $$('input, textarea', form).forEach(function (inp) {
      inp.addEventListener('input', function () {
        var f = inp.closest('.field');
        if (f) f.classList.remove('has-error');
      });
    });
  });

  /* ---------- 15. Развернуть длинный отзыв ---------- */
  $$('.review__more').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var r = btn.closest('.review');
      var open = r.classList.toggle('is-expanded');
      btn.textContent = open ? 'Свернуть' : 'Читать полностью';
      // высота слайда изменилась — пересчитываем слайдер
      var sl = r.closest('.swiper');
      if (sl && sl.swiper) sl.swiper.update();
    });
  });

  /* ---------- 16. Фильтр новостей ---------- */
  var fb = $('.filter-bar');
  if (fb) {
    fb.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      $$('button', fb).forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var tag = btn.getAttribute('data-filter');
      $$('[data-news-item]').forEach(function (item) {
        var show = tag === 'all' || item.getAttribute('data-tag') === tag;
        item.classList.toggle('hidden', !show);
      });
    });
  }

  /* ---------- 17. Активный пункт меню ---------- */
  var page = document.body.getAttribute('data-page');
  if (page) {
    $$('a[data-nav="' + page + '"]').forEach(function (a) { a.classList.add('is-active'); });
  }

  /* ---------- 18. Год в подвале ---------- */
  $$('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });

  /* ---------- 18.1 Поделиться статьёй ---------- */
  var shareBtns = $$('[data-share]');
  if (shareBtns.length) {
    var url = encodeURIComponent(location.href);
    var ttl = encodeURIComponent(document.title.split(' — ')[0]);
    shareBtns.forEach(function (el) {
      var kind = el.getAttribute('data-share');
      if (kind === 'tg') el.setAttribute('href', 'https://t.me/share/url?url=' + url + '&text=' + ttl);
      if (kind === 'vk') el.setAttribute('href', 'https://vk.com/share.php?url=' + url + '&title=' + ttl);
      if (kind === 'copy') {
        el.addEventListener('click', function () {
          var done = function () {
            el.classList.add('is-done');
            el.setAttribute('aria-label', 'Ссылка скопирована');
            setTimeout(function () { el.classList.remove('is-done'); }, 1800);
          };
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(location.href).then(done, function () {});
          } else {
            var t = document.createElement('textarea');
            t.value = location.href;
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); done(); } catch (err) {}
            document.body.removeChild(t);
          }
        });
      }
    });
  }

  /* ---------- 19. Видео-перебивки: играют только в зоне видимости ---------- */
  var loops = $$('video[data-autoloop]');
  if (loops.length && 'IntersectionObserver' in window) {
    var vo = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        var v = en.target;
        if (en.isIntersecting && !reduced) {
          if (v.preload === 'none') v.preload = 'auto';
          var pr = v.play();
          if (pr && pr.catch) pr.catch(function () {});
        } else if (!v.paused) {
          v.pause();
        }
      });
    }, { threshold: 0.35 });
    loops.forEach(function (v) { vo.observe(v); });
  }

  /* ---------- 19.1 Карта: включается по клику ----------
     Иначе колесо мыши над картой зумит её, а не прокручивает страницу. */
  $$('.map-wrap').forEach(function (wrap) {
    if ($('.map-lock', wrap)) return;
    var lock = document.createElement('button');
    lock.type = 'button';
    lock.className = 'map-lock';
    lock.setAttribute('aria-label', 'Включить карту');
    lock.innerHTML = '<span><svg viewBox="0 0 24 24"><path d="M12 21.2s7-5.7 7-11.2a7 7 0 10-14 0c0 5.5 7 11.2 7 11.2z"/>' +
                     '<circle cx="12" cy="10" r="2.6"/></svg>Нажмите, чтобы включить карту</span>';
    wrap.appendChild(lock);

    var enable = function () { wrap.classList.add('is-active'); };
    lock.addEventListener('click', enable);
    // при уходе курсора карта снова «замирает», чтобы не мешать прокрутке
    wrap.addEventListener('mouseleave', function () { wrap.classList.remove('is-active'); });
  });

  /* ---------- 20. Лайтбокс фотогалереи ---------- */
  var groups = $$('.gallery, .gallery-strip');
  if (groups.length) {
    var box = document.createElement('div');
    box.className = 'lightbox';
    box.setAttribute('role', 'dialog');
    box.setAttribute('aria-modal', 'true');
    box.setAttribute('aria-label', 'Просмотр фотографии');
    box.innerHTML =
      '<div class="lightbox__backdrop" data-lb-close></div>' +
      '<div class="lightbox__stage"><img class="lightbox__img" alt=""></div>' +
      '<button class="lightbox__btn lightbox__close" type="button" data-lb-close aria-label="Закрыть (Esc)">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">' +
        '<path d="M5 5l14 14M19 5L5 19"/></svg></button>' +
      '<button class="lightbox__btn lightbox__prev" type="button" data-lb-prev aria-label="Предыдущая фотография">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M19 12H5M11 18l-6-6 6-6"/></svg></button>' +
      '<button class="lightbox__btn lightbox__next" type="button" data-lb-next aria-label="Следующая фотография">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M5 12h14M13 6l6 6-6 6"/></svg></button>' +
      '<div class="lightbox__counter"><b><span data-lb-i>1</span></b> / <span data-lb-n>1</span></div>';
    document.body.appendChild(box);

    var lbImg  = $('.lightbox__img', box);
    var lbI    = $('[data-lb-i]', box);
    var lbN    = $('[data-lb-n]', box);
    var lbPrev = $('[data-lb-prev]', box);
    var lbNext = $('[data-lb-next]', box);

    var list = [], idx = 0, lastFocus = null;

    var srcOf = function (fig) {
      var img = fig.querySelector('img');
      return { src: img.getAttribute('src'), alt: img.getAttribute('alt') || '' };
    };

    var preload = function (n) {
      var f = list[n];
      if (!f) return;
      var i = new Image();
      i.src = srcOf(f).src;
    };

    var show = function (n) {
      idx = (n + list.length) % list.length;
      var d = srcOf(list[idx]);
      lbImg.classList.remove('is-shown');
      var swap = function () {
        lbImg.src = d.src;
        lbImg.alt = d.alt;
        if (lbImg.complete) lbImg.classList.add('is-shown');
        else lbImg.onload = function () { lbImg.classList.add('is-shown'); };
      };
      if (reduced) swap(); else setTimeout(swap, 110);
      lbI.textContent = idx + 1;
      lbN.textContent = list.length;
      var single = list.length < 2;
      lbPrev.disabled = single;
      lbNext.disabled = single;
      preload(idx + 1);
      preload(idx - 1);
    };

    var openLb = function (figs, n, trigger) {
      list = figs;
      lastFocus = trigger || null;
      show(n);
      box.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      $('.lightbox__close', box).focus({ preventScroll: true });
    };

    var closeLb = function () {
      box.classList.remove('is-open');
      document.body.style.overflow = '';
      lbImg.classList.remove('is-shown');
      if (lastFocus) lastFocus.focus({ preventScroll: true });
    };

    groups.forEach(function (group) {
      var figs = $$('figure', group);
      figs.forEach(function (fig, i) {
        fig.setAttribute('role', 'button');
        fig.setAttribute('tabindex', '0');
        fig.setAttribute('aria-label', 'Открыть фотографию ' + (i + 1) + ' из ' + figs.length);
        fig.addEventListener('click', function () { openLb(figs, i, fig); });
        fig.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openLb(figs, i, fig); }
        });
      });
    });

    $$('[data-lb-close]', box).forEach(function (el) {
      el.addEventListener('click', closeLb);
    });
    lbPrev.addEventListener('click', function () { show(idx - 1); });
    lbNext.addEventListener('click', function () { show(idx + 1); });

    document.addEventListener('keydown', function (e) {
      if (!box.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLb();
      else if (e.key === 'ArrowLeft') show(idx - 1);
      else if (e.key === 'ArrowRight') show(idx + 1);
    });

    // смахивание на сенсорных экранах
    var tx = 0, ty = 0;
    box.addEventListener('touchstart', function (e) {
      tx = e.changedTouches[0].clientX;
      ty = e.changedTouches[0].clientY;
    }, { passive: true });
    box.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - tx;
      var dy = e.changedTouches[0].clientY - ty;
      if (Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy)) show(idx + (dx < 0 ? 1 : -1));
      else if (dy > 90 && Math.abs(dy) > Math.abs(dx)) closeLb();
    }, { passive: true });
  }

})();
