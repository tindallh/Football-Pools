<?php
/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi                                */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
/*                                                                      */
/* APPROVE MEMBERSHIP Version 2.0                                       */
/*                                                                      */
/* Displays a list of pending membership applications and allows        */
/* the administrator to either approve the application or delete it.    */
/* If approved, the standard email is sent to the user with the         */
/* activation link. The time allowed for activation has been increased  */
/* to 48 hours. If the application is rejected, the administrator has   */
/* the option of sending the applicant an email explaining the reasons  */
/* for the rejection. The rejection email message is customisable.      */
/* A customisable follow up email can be sent to give or receive further*/
/* information from the applicant. A customisable message can be added  */
/* to the activation email                                              */
/*                                                                      */
/* Module created by Kenneth Arnold                                     */
/* Copyright (c) 2003 by Kenneth Arnold                                 */
/* released under GPL licence                                           */
/*                                                                      */
/* arnoldkrg@hotmail.com                                                */
/*                                                                      */
/************************************************************************/
define("_NEWMEMBERAPP"," A new user application has been received for");
define("_PENDINGNOTICE","Once your application is approved, You will receive a confirmation email with a link to a page you should visit to activate your account within 48 hours of receipt.");
define("_FINISHUSERCONF1","Your request for a new account is being processed. Once your application has been approved, you'll receive an email with an activation link that should be visited within 48 hours of receipt to be able to activate your account.");
define("_UEMAIL","-Email:");
define("_MEMBERAPP","New User Application");
define("_YOUAREREGISTERED1","Welcome! Your application details have been recorded.");
?>