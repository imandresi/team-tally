(()=>{var e={889:()=>{document.addEventListener("DOMContentLoaded",(()=>{!function(){const e=document.querySelector(".teamtally__teams__edit_team h1.wp-heading-inline");if(!e)return;const t=document.querySelector(".teamtally__teams__edit_team #teams_list_url").value;if(t){const a=document.createElement("a");a.setAttribute("class","page-title-action"),a.setAttribute("href",t),a.setAttribute("style","margin-left: 20px;"),a.textContent="List Teams",e.append(a)}}()}))}},t={};function a(l){var o=t[l];if(void 0!==o)return o.exports;var d=t[l]={exports:{}};return e[l](d,d.exports,a),d.exports}(()=>{"use strict";var e;const t={mediaUploader:e};var l,o,d,n,u,r,s;function i(e){const t=e.target.dataset.leagueId,a=document.querySelector(`.league-item-id-${t} .league-name`),l=a?.textContent,o=document.querySelector(`.league-item-id-${t} .teamtally-delete`),d=o?.dataset.removeUrl;confirm(`Would you really want to delete the league "${l}" ?`)&&d&&(window.location.href=d)}t.openMediaUploader=t=>{e||(e=wp.media({title:"Select Image",button:{text:"Use this image"},multiple:!1})).on("select",(()=>{t(e)})),e.open()},t.executeIfSelectorExists=(e,t)=>{document.querySelector(e)&&t()},t.executeIfSelectorExists("body.team-tally_page_teamtally_leagues_add",(()=>{document.addEventListener("DOMContentLoaded",(()=>{l=document.querySelector(".teamtally_leagues__add-league #photo-upload"),o=document.querySelector(".teamtally_leagues__add-league #photo-remove"),n=document.querySelector("form#add-league input[name=league-photo]"),r=document.querySelector("form#add-league input[name=former-league-photo]"),s=document.querySelector("form#add-league input[name=former-league-photo-url]"),u=document.querySelector(".teamtally_leagues__add-league__photo"),d=document.querySelector(".teamtally_leagues__add-league .submit .spinner"),l.addEventListener("click",(()=>{t.openMediaUploader((e=>{var t=e.state().get("selection").first().toJSON();n.value=t.id,u.style.backgroundImage=`url(${t.url})`,u.classList.remove("invalid"),l.classList.add("hidden"),o.classList.remove("hidden")}))})),o.addEventListener("click",(()=>(n.value="",u.style.backgroundImage="none",l.classList.remove("hidden"),o.classList.add("hidden"),u.classList.remove("invalid"),void(r&&(n.value=r.value,u.style.backgroundImage=`url(${s.value})`)))));const e=document.querySelector(".teamtally_leagues__add-league form");e.reset(),n.value=r.value,e.addEventListener("submit",(e=>{if(!+n.value)return u.classList.add("invalid"),void e.preventDefault();d.classList.add("is-active")}))}))})),t.executeIfSelectorExists("body.team-tally_page_teamtally_leagues_view",(()=>{document.addEventListener("DOMContentLoaded",(()=>{document.querySelectorAll(".teamtally_leagues__league-item .teamtally-delete").forEach((e=>{e.addEventListener("click",i)}));const e=document.querySelector(".teamtally_leagues__league-item_new");e.addEventListener("click",(()=>{const t=e.dataset.newLeagueUrl;window.location.href=t}))}))})),a(889),window.TEAMTALLY={leagues:{}}})()})();