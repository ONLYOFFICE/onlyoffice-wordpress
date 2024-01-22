/**
 * (c) Copyright Ascensio System SIA 2023
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

(function () {
  if (!window.ONLYOFFICE) window.ONLYOFFICE = {};

  window.ONLYOFFICE.formatsUtils = {
    getDocumentType: function (fileName) {
      const extension = this.getExtension(fileName);

      for (let i = 0; i < ONLYOFFICE.formats.length; i++) {
        if (ONLYOFFICE.formats[i].name === extension) return ONLYOFFICE.formats[i].type;
      }

      return null;
    },

    getExtension: function (fileName) {
      var parts = fileName.toLowerCase().split(".");

      return parts.pop();
    },

    getFileName: async function(id) {
      const mediaMeta = await wp.apiFetch({ path: `/wp/v2/media/${id}` });
      if (mediaMeta != null &&  mediaMeta.hasOwnProperty('source_url')) {
          const filePath = mediaMeta['source_url'];
          const baseUrl = filePath.split('?')[0];
          const baseName = baseUrl.split('\\').pop().split('/').pop();

          return baseName;
      }

      return null;
    },

    getViewableExtensions: function( ) {
      return ONLYOFFICE.formats
        .filter((format) => format.actions.includes('view'))
        .map(format => format.name);
    }
  }
})();
