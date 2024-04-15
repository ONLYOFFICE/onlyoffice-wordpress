/*
 * (c) Copyright Ascensio System SIA 2024
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

import { RawHTML } from '@wordpress/element';

const Save = ( { attributes } ) => {
    if ( !attributes.id ) {
        return '';
    }

    let parameters = '';

    if ( attributes.hasOwnProperty('id')) {
        parameters += 'id=' + attributes.id + ' ';
    }

    if ( attributes.hasOwnProperty('documentView') && attributes.documentView.length > 0 ) {
        parameters += 'documentView=' + attributes.documentView + ' ';
    }

    if ( attributes.hasOwnProperty('inNewTab')) {
        parameters += 'inNewTab=' + attributes.inNewTab + ' ';
    }

    if ( attributes.hasOwnProperty('align') && attributes.align.length > 0 ) {
        parameters += 'align=' + attributes.align + ' ';
    }

    if ( attributes.hasOwnProperty('width') && attributes.width.length > 0 ) {
        parameters += 'width=' + attributes.width + ' ';
    }

    if ( attributes.hasOwnProperty('height') && attributes.height.length > 0 ) {
        parameters += 'height=' + attributes.height + ' ';
    }

    if ( attributes.hasOwnProperty('showOpenButton') ) {
        parameters += 'showOpenButton=' + attributes.showOpenButton + ' ';
    }

    if ( attributes.hasOwnProperty('openButtonText') && attributes.openButtonText.length > 0 ) {
        parameters += 'openButtonText=' + attributes.openButtonText + ' ';
    }

    return <RawHTML>{ `[onlyoffice ${ parameters } /]` }</RawHTML>;
};
export default Save;
