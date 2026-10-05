import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient } from '@angular/common/http';
import { AppConfigRoutes } from './app.routes';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(AppConfigRoutes.routes),
    provideHttpClient()
  ]
};